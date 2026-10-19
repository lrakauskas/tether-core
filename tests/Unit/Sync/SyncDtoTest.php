<?php

use Tether\Core\Sync\PullRequest;
use Tether\Core\Sync\PullResult;
use Tether\Core\Sync\PushConflict;
use Tether\Core\Sync\PushRequest;
use Tether\Core\Sync\PushRejection;
use Tether\Core\Sync\PushResult;
use Tether\Core\Sync\Snapshot;
use Tether\Core\Sync\SyncStatus;

it('hydrates push requests and serializes push results', function () {
    $request = PushRequest::fromArray([
        'client_id' => 'client-1',
        'mutations' => [[
            'mutation_id' => '01HXYZ0000AABBCCDD0011AAB1',
            'entity_id' => '01HXYZ0001AABBCCDD0011AAB1',
            'model' => 'Post',
            'operation' => 'create',
            'payload' => ['title' => 'Hello'],
            'version' => 1,
            'timestamp' => 1000,
        ]],
    ]);

    $result = new PushResult(
        applied: [$request->mutations[0]->getMutationId()],
        rejected: [new PushRejection('bad-id', 'validation_failed', ['messages' => []])],
        conflicts: [new PushConflict('conflict-id', data: ['server_state' => ['title' => 'Server']])],
    );

    expect($request->clientId)->toBe('client-1')
        ->and($request->mutations[0]->getPayload())->toBe(['title' => 'Hello'])
        ->and($result->toArray()['applied'])->toBe(['01HXYZ0000AABBCCDD0011AAB1'])
        ->and($result->conflicts[0]->serverState())->toBe(['title' => 'Server']);
});

it('hydrates pull requests and serializes pull results', function () {
    $request = PullRequest::fromArray([
        'client_id' => 'client-1',
        'last_sync_cursor' => 123,
        'limit' => 50,
    ]);

    $snapshot = new Snapshot('entity-1', 'Post', 'upsert', ['title' => 'Hello']);
    $result = new PullResult([$snapshot], 456, true);

    expect($request->clientId)->toBe('client-1')
        ->and($request->cursor)->toBe(123)
        ->and($request->limit)->toBe(50)
        ->and($snapshot->withPayload(['title' => 'Changed'])->payload)->toBe(['title' => 'Changed'])
        ->and(PullResult::fromArray($result->toArray())->toArray())->toBe($result->toArray());
});

it('serializes sync status for diagnostics', function () {
    $status = new SyncStatus(1, 2, 3, '456', '2026-05-16T12:00:00+00:00');

    expect($status->toArray())->toBe([
        'pending' => 1,
        'failed' => 2,
        'conflicts' => 3,
        'last_sync_cursor' => '456',
        'last_sync_at' => '2026-05-16T12:00:00+00:00',
    ]);
});

it('round-trips pull request cursors without losing integer precision', function (?int $cursor, ?int $limit) {
    $wire = ['client_id' => 'client-1', 'last_sync_cursor' => $cursor];
    if ($limit !== null) {
        $wire['limit'] = $limit;
    }

    $request = new PullRequest('client-1', $cursor, $limit);
    expect($request->toArray())->toBe($wire)
        ->and(PullRequest::fromArray($wire)->toArray())->toBe($wire);
})->with([
    'initial pull' => [null, null],
    'zero cursor' => [0, 10],
    'microsecond cursor' => [1_778_943_000_456_789, 50],
]);

it('defaults absent and null pull options to an initial unlimited request', function (array $options) {
    $request = PullRequest::fromArray(['client_id' => 'client-1', ...$options]);

    expect($request->toArray())->toBe(['client_id' => 'client-1', 'last_sync_cursor' => null]);
})->with([
    'absent' => [[]],
    'null' => [['last_sync_cursor' => null, 'limit' => null]],
]);

it('round-trips a batch of mutations through the push wire format', function () {
    $wire = [
        'client_id' => 'client-1',
        'mutations' => [
            [
                'mutation_id' => 'create-1', 'entity_id' => 'entity-1', 'model' => 'Post',
                'operation' => 'create', 'payload' => ['title' => 'Hello'], 'version' => 1, 'timestamp' => 1000,
            ],
            [
                'mutation_id' => 'delete-1', 'entity_id' => 'entity-2', 'model' => 'Comment',
                'operation' => 'delete', 'payload' => [], 'version' => 2, 'timestamp' => 2000,
            ],
        ],
    ];

    expect(PushRequest::fromArray($wire)->toArray())->toBe($wire)
        ->and(PushRequest::fromArray(['client_id' => 'client-1'])->toArray())
        ->toBe(['client_id' => 'client-1', 'mutations' => []]);
});

it('accumulates push outcomes without changing previous results', function () {
    $empty = new PushResult();
    $applied = $empty->withApplied('accepted-1');
    $rejected = $applied->withRejected(new PushRejection('rejected-1', 'validation_failed', ['title' => ['Required']]));
    $complete = $rejected->withConflict(new PushConflict('conflict-1', data: ['server_state' => ['title' => 'Server']]));

    expect($empty->toArray())->toBe(['applied' => [], 'rejected' => [], 'conflicts' => []])
        ->and($applied->rejected)->toBe([])
        ->and($rejected->conflicts)->toBe([])
        ->and($complete->toArray())->toBe([
            'applied' => ['accepted-1'],
            'rejected' => [['mutation_id' => 'rejected-1', 'reason' => 'validation_failed', 'data' => ['title' => ['Required']]]],
            'conflicts' => [['mutation_id' => 'conflict-1', 'reason' => 'conflict', 'data' => ['server_state' => ['title' => 'Server']]]],
        ])
        ->and(PushResult::fromArray($complete->toArray())->toArray())->toBe($complete->toArray())
        ->and(PushResult::fromArray([])->toArray())->toBe($empty->toArray());
});

it('preserves rejection objects so empty data retains its JSON object shape', function () {
    $data = new stdClass();
    $rejection = PushRejection::fromArray(['mutation_id' => 'bad-1', 'reason' => 'invalid', 'data' => $data]);

    expect($rejection->data)->toBe($data)
        ->and(json_encode($rejection->toArray(), JSON_THROW_ON_ERROR))
        ->toBe('{"mutation_id":"bad-1","reason":"invalid","data":{}}')
        ->and(PushRejection::fromArray(['mutation_id' => 'bad-2', 'reason' => 'invalid'])->data)->toBe([]);
});

it('hydrates default and custom conflict reasons with optional server state', function () {
    $default = PushConflict::fromArray(['mutation_id' => 'conflict-1']);
    $wire = [
        'mutation_id' => 'conflict-2',
        'reason' => 'version_conflict',
        'data' => ['server_state' => ['title' => 'Server'], 'server_updated_at' => '2026-10-19T12:00:00Z'],
    ];
    $custom = PushConflict::fromArray($wire);

    expect($default->reason)->toBe('conflict')
        ->and($default->serverState())->toBe([])
        ->and($custom->serverState())->toBe(['title' => 'Server'])
        ->and($custom->toArray())->toBe($wire);
});

it('hydrates empty pull results and snapshots with no payload', function () {
    expect(PullResult::fromArray([])->toArray())
        ->toBe(['snapshots' => [], 'new_sync_cursor' => null, 'has_more' => false])
        ->and(Snapshot::fromArray(['entity_id' => 'entity-1', 'model' => 'Post', 'operation' => 'delete'])->toArray())
        ->toBe(['entity_id' => 'entity-1', 'model' => 'Post', 'operation' => 'delete', 'payload' => []]);
});
