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
