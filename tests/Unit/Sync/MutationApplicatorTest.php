<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\Models\EmptyFieldsPost;
use Tests\Models\RestrictedPost;
use Tests\Models\SoftDeletedPost;
use Tests\Models\TestPost;
use Tests\Models\UnrestrictedPost;
use Tether\Core\Enums\OperationType;
use Tether\Core\Exceptions\EntityNotFoundException;
use Tether\Core\Exceptions\MutationValidationException;
use Tether\Core\Mutation\Mutation;
use Tether\Core\Sync\MutationApplicator;

beforeEach(function () {
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->tetherUlid();
        $table->tetherUlid('sync_id');
        $table->string('title')->nullable();
        $table->text('body')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
});

function incomingMutation(
    OperationType $operation = OperationType::Create,
    array $payload = ['title' => 'Incoming', 'body' => 'Body'],
    string $model = 'TestPost',
): Mutation {
    return new Mutation('mutation-1', 'entity-1', $model, $operation, $payload, 1, 1_700_000_000_000);
}

it('creates by sync identity while preserving the local primary key and guarding fields', function () {
    $applicator = new MutationApplicator('Tests\\Models\\');
    $applicator->apply(incomingMutation(payload: [
        'title' => 'Incoming',
        'body' => 'Body',
        'id' => 999,
        'tether_id' => 'replacement-id',
    ], model: 'Remote\\Models\\TestPost'));

    $post = TestPost::sole();

    expect($post->getKey())->toBe(1)
        ->and($post->tether_id)->toBe('entity-1')
        ->and($post->title)->toBe('Incoming')
        ->and($post->body)->toBe('Body');
});

it('replays create mutations without duplicates and updates the existing record', function () {
    $applicator = new MutationApplicator('Tests\\Models');
    $applicator->apply(incomingMutation());
    $id = TestPost::sole()->getKey();

    $applicator->apply(incomingMutation(payload: ['title' => 'Replayed']));
    $applicator->apply(incomingMutation(payload: ['title' => 'Replayed']));

    $post = TestPost::sole();
    expect($post->getKey())->toBe($id)
        ->and($post->title)->toBe('Replayed')
        ->and($post->body)->toBe('Body');
});

it('updates only the supplied attributes of the matching entity', function () {
    $applicator = new MutationApplicator('Tests\\Models');
    $applicator->apply(incomingMutation());

    $applicator->apply(incomingMutation(OperationType::Update, ['title' => 'Updated']));

    expect(TestPost::sole()->title)->toBe('Updated')
        ->and(TestPost::sole()->body)->toBe('Body');
});

it('uses a configured sync key for create update and delete', function () {
    $applicator = new MutationApplicator('Tests\\Models', 'sync_id');
    $applicator->apply(incomingMutation());

    expect(TestPost::sole()->sync_id)->toBe('entity-1')
        ->and(TestPost::sole()->tether_id)->toBeNull();

    $applicator->apply(incomingMutation(OperationType::Update, ['title' => 'Updated']));
    expect(TestPost::sole()->title)->toBe('Updated');

    $applicator->apply(incomingMutation(OperationType::Delete, []));
    expect(TestPost::count())->toBe(0);
});

it('reports structured missing-entity errors', function (OperationType $operation) {
    try {
        (new MutationApplicator('Tests\\Models'))->apply(incomingMutation($operation));
        $this->fail('A missing entity must be rejected.');
    } catch (EntityNotFoundException $exception) {
        expect($exception->model)->toBe('TestPost')
            ->and($exception->entityId)->toBe('entity-1')
            ->and($exception->getMessage())->toBe('Entity [TestPost:entity-1] not found.');
    }
})->with([OperationType::Update, OperationType::Delete]);

it('reports the resolved class when the model is missing', function () {
    (new MutationApplicator('Tests\\Models'))->apply(incomingMutation(model: 'MissingPost'));
})->throws(RuntimeException::class, 'Tether model class [Tests\\Models\\MissingPost] not found.');

it('respects soft deletes when applying a delete mutation', function () {
    $applicator = new MutationApplicator('Tests\\Models');
    $applicator->apply(incomingMutation(model: 'SoftDeletedPost'));
    $applicator->apply(incomingMutation(OperationType::Delete, [], 'SoftDeletedPost'));

    expect(SoftDeletedPost::count())->toBe(0)
        ->and(SoftDeletedPost::withTrashed()->sole()->deleted_at)->not->toBeNull();
});

it('filters incoming fields before both creating and updating', function (string $model, ?string $title, ?string $body) {
    $applicator = new MutationApplicator('Tests\\Models');
    $applicator->apply(incomingMutation(model: $model));
    $post = TestPost::sole();
    expect($post->title)->toBe($title)
        ->and($post->body)->toBe($body);

    $applicator->apply(incomingMutation(OperationType::Update, ['title' => 'Updated', 'body' => 'Changed'], $model));
    $post->refresh();
    expect($post->title)->toBe($title === null ? null : 'Updated')
        ->and($post->body)->toBe($body === null ? null : 'Changed');
})->with([
    'no field method' => [TestPost::class, 'Incoming', 'Body'],
    'explicit fields' => [RestrictedPost::class, 'Incoming', null],
    'null fields use fillable' => [UnrestrictedPost::class, 'Incoming', 'Body'],
    'empty fields allow nothing' => [EmptyFieldsPost::class, null, null],
]);

it('converts model validation errors without persisting invalid state', function (OperationType $operation) {
    $applicator = new MutationApplicator('Tests\\Models');
    if ($operation === OperationType::Update) {
        $applicator->apply(incomingMutation(model: 'ValidatingPost'));
    }

    try {
        $applicator->apply(incomingMutation($operation, ['title' => ''], 'ValidatingPost'));
        $this->fail('Invalid state must be rejected.');
    } catch (MutationValidationException $exception) {
        expect($exception->messages)->toBe(['title' => ['The title field is required.']])
            ->and($exception->getMessage())->toBe('Mutation [mutation-1] failed validation.');
    }

    expect(TestPost::count())->toBe($operation === OperationType::Create ? 0 : 1);
    if ($operation === OperationType::Update) {
        expect(TestPost::sole()->title)->toBe('Incoming');
    }
})->with([OperationType::Create, OperationType::Update]);

it('suppresses model events during replay and restores them afterward', function () {
    $events = [];
    foreach (['creating', 'updating', 'deleting'] as $event) {
        TestPost::{$event}(function () use (&$events, $event) {
            $events[] = $event;
        });
    }

    $applicator = new MutationApplicator('Tests\\Models');
    $applicator->apply(incomingMutation());
    $applicator->apply(incomingMutation(OperationType::Update, ['title' => 'Updated']));
    $applicator->apply(incomingMutation(OperationType::Delete, []));
    expect($events)->toBe([]);

    TestPost::create(['title' => 'Local']);
    expect($events)->toBe(['creating']);
});

it('restores the event dispatcher when applying a mutation throws', function () {
    $dispatcher = TestPost::getEventDispatcher();
    expect(fn () => (new MutationApplicator('Tests\\Models'))->apply(incomingMutation(OperationType::Update)))
        ->toThrow(EntityNotFoundException::class);

    expect(TestPost::getEventDispatcher())->toBe($dispatcher);
});
