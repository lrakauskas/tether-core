<?php

use Tether\Core\Enums\OperationType;
use Tether\Core\Mutation\Mutation;
use Tether\Core\Mutation\MutationSerializer;

function makeSerializerMutation(array $overrides = []): Mutation
{
    return new Mutation(
        mutationId: $overrides['mutation_id'] ?? '01HXYZ0001AABBCCDD0011AABB',
        entityId:   $overrides['entity_id']   ?? '01HXYZ0002AABBCCDD0011AABB',
        model:      $overrides['model']       ?? 'Post',
        operation:  $overrides['operation']   ?? OperationType::Create,
        payload:    $overrides['payload']     ?? ['title' => 'Hello', 'body' => 'World'],
        version:    $overrides['version']     ?? 1,
        timestamp:  $overrides['timestamp']   ?? 1_700_000_000_000,
    );
}

it('serializes a mutation to the correct array shape', function () {
    $serializer = new MutationSerializer();
    $mutation = makeSerializerMutation();

    $array = $serializer->toArray($mutation);

    expect($array)->toBe([
        'mutation_id' => '01HXYZ0001AABBCCDD0011AABB',
        'entity_id'   => '01HXYZ0002AABBCCDD0011AABB',
        'model'       => 'Post',
        'operation'   => 'create',
        'payload'     => ['title' => 'Hello', 'body' => 'World'],
        'version'     => 1,
        'timestamp'   => 1_700_000_000_000,
    ]);
});

it('deserializes an array back into a Mutation value object', function () {
    $serializer = new MutationSerializer();

    $array = [
        'mutation_id' => '01HXYZ0001AABBCCDD0011AABB',
        'entity_id'   => '01HXYZ0002AABBCCDD0011AABB',
        'model'       => 'Comment',
        'operation'   => 'update',
        'payload'     => ['body' => 'Edited'],
        'version'     => 3,
        'timestamp'   => 1_700_000_999_000,
    ];

    $mutation = $serializer->fromArray($array);

    expect($mutation)->toBeInstanceOf(Mutation::class)
        ->and($mutation->getMutationId())->toBe('01HXYZ0001AABBCCDD0011AABB')
        ->and($mutation->getEntityId())->toBe('01HXYZ0002AABBCCDD0011AABB')
        ->and($mutation->getModel())->toBe('Comment')
        ->and($mutation->getOperation())->toBe(OperationType::Update)
        ->and($mutation->getPayload())->toBe(['body' => 'Edited'])
        ->and($mutation->getVersion())->toBe(3)
        ->and($mutation->getTimestamp())->toBe(1_700_000_999_000);
});

it('round-trips through toArray and fromArray without data loss', function () {
    $serializer = new MutationSerializer();
    $original = makeSerializerMutation();

    $restored = $serializer->fromArray($serializer->toArray($original));

    expect($restored->getMutationId())->toBe($original->getMutationId())
        ->and($restored->getEntityId())->toBe($original->getEntityId())
        ->and($restored->getModel())->toBe($original->getModel())
        ->and($restored->getOperation())->toBe($original->getOperation())
        ->and($restored->getPayload())->toBe($original->getPayload())
        ->and($restored->getVersion())->toBe($original->getVersion())
        ->and($restored->getTimestamp())->toBe($original->getTimestamp());
});

it('serializes all operation types correctly', function (OperationType $operation, string $expected) {
    $serializer = new MutationSerializer();
    $mutation = makeSerializerMutation(['operation' => $operation]);

    expect($serializer->toArray($mutation)['operation'])->toBe($expected);
})->with([
    'create' => [OperationType::Create, 'create'],
    'update' => [OperationType::Update, 'update'],
    'delete' => [OperationType::Delete, 'delete'],
]);

it('throws on fromArray with invalid operation value', function () {
    $serializer = new MutationSerializer();

    $serializer->fromArray([
        'mutation_id' => '01HXYZ0001AABBCCDD0011AABB',
        'entity_id'   => '01HXYZ0002AABBCCDD0011AABB',
        'model'       => 'Post',
        'operation'   => 'patch',
        'payload'     => [],
        'version'     => 1,
        'timestamp'   => 1_700_000_000_000,
    ]);
})->throws(\ValueError::class);
