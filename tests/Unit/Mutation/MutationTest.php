<?php

use Tether\Core\Enums\OperationType;
use Tether\Core\Mutation\Mutation;

function makeMutation(array $overrides = []): Mutation
{
    return new Mutation(
        mutationId: $overrides['mutation_id'] ?? '01HXYZ0001AABBCCDD0011AABB',
        entityId:   $overrides['entity_id']   ?? '01HXYZ0002AABBCCDD0011AABB',
        model:      $overrides['model']       ?? 'Post',
        operation:  $overrides['operation']   ?? OperationType::Create,
        payload:    $overrides['payload']     ?? ['title' => 'Hello'],
        version:    $overrides['version']     ?? 1,
        timestamp:  $overrides['timestamp']   ?? 1_700_000_000_000,
    );
}

it('stores and returns all fields', function () {
    $mutation = makeMutation();

    expect($mutation->getMutationId())->toBe('01HXYZ0001AABBCCDD0011AABB')
        ->and($mutation->getEntityId())->toBe('01HXYZ0002AABBCCDD0011AABB')
        ->and($mutation->getModel())->toBe('Post')
        ->and($mutation->getOperation())->toBe(OperationType::Create)
        ->and($mutation->getPayload())->toBe(['title' => 'Hello'])
        ->and($mutation->getVersion())->toBe(1)
        ->and($mutation->getTimestamp())->toBe(1_700_000_000_000);
});

it('is immutable - properties cannot be modified', function () {
    $mutation = makeMutation();
    $reflection = new ReflectionClass($mutation);

    foreach ($reflection->getProperties() as $property) {
        expect($property->isReadOnly())->toBeTrue(
            "Property {$property->getName()} must be readonly"
        );
    }
});

it('supports all operation types', function (OperationType $operation) {
    $mutation = makeMutation(['operation' => $operation]);

    expect($mutation->getOperation())->toBe($operation);
})->with([
    'create' => [OperationType::Create],
    'update' => [OperationType::Update],
    'delete' => [OperationType::Delete],
]);

it('accepts an empty payload for delete operations', function () {
    $mutation = makeMutation(['operation' => OperationType::Delete, 'payload' => []]);

    expect($mutation->getPayload())->toBeEmpty();
});

it('creates a changed copy with withPayload', function () {
    $mutation = makeMutation(['payload' => ['title' => 'Original']]);

    $changed = $mutation->withPayload(['title' => 'Changed']);

    expect($changed)->not->toBe($mutation)
        ->and($mutation->getPayload())->toBe(['title' => 'Original'])
        ->and($changed->getPayload())->toBe(['title' => 'Changed'])
        ->and($changed->getMutationId())->toBe($mutation->getMutationId());
});

it('serializes and hydrates itself without the compatibility serializer', function () {
    $mutation = makeMutation(['operation' => OperationType::Update]);

    $restored = Mutation::fromArray($mutation->toArray());

    expect($restored->toArray())->toBe($mutation->toArray());
});
