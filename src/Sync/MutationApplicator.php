<?php

namespace Tether\Core\Sync;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Tether\Core\Contracts\MutationInterface;
use Tether\Core\Enums\OperationType;
use Tether\Core\Exceptions\EntityNotFoundException;
use Tether\Core\Exceptions\MutationValidationException;

/**
 * Applies a mutation to the local database by resolving the target Eloquent model
 * from the configured namespace and performing the appropriate create/update/delete.
 *
 * All operations run inside Model::withoutEvents() so that traits such as Syncable
 * do not fire mutation-logging callbacks when replaying incoming server mutations.
 *
 * Create operations are idempotent: if a record with the same sync key already
 * exists it is updated rather than duplicated.
 */
class MutationApplicator
{
    public function __construct(
        private readonly string $modelNamespace,
        private readonly string $syncKeyColumn = 'tether_id',
    ) {}

    /**
     * Apply a mutation to the database.
     *
     * @throws \RuntimeException when the resolved model class does not exist.
     */
    public function apply(MutationInterface $mutation): void
    {
        $modelClass = rtrim($this->modelNamespace, '\\').'\\'.class_basename($mutation->getModel());

        if (!class_exists($modelClass)) {
            throw new \RuntimeException("Tether model class [{$modelClass}] not found.");
        }

        // withoutEvents prevents Syncable (and other observers) from firing during
        // application of incoming mutations, avoiding double-logging and ULID re-assignment.
        Model::withoutEvents(function () use ($modelClass, $mutation) {
            match ($mutation->getOperation()) {
                OperationType::Create => $this->applyCreate($modelClass, $mutation),
                OperationType::Update => $this->applyUpdate($modelClass, $mutation),
                OperationType::Delete => $this->applyDelete($modelClass, $mutation),
            };
        });
    }

    private function applyCreate(string $modelClass, MutationInterface $mutation): void
    {
        $existing = $modelClass::where($this->syncKeyColumn, $mutation->getEntityId())->first();

        if ($existing !== null) {
            $existing->fill($this->filterPayload($existing, $mutation->getPayload()));
            $this->saveOrThrow($existing, $mutation->getMutationId());

            return;
        }

        /** @var Model $model */
        $model = new $modelClass();
        // setAttribute bypasses $fillable so the sync key column is always set.
        $model->setAttribute($this->syncKeyColumn, $mutation->getEntityId());
        $model->fill($this->filterPayload($model, $mutation->getPayload()));
        $this->saveOrThrow($model, $mutation->getMutationId());
    }

    private function applyUpdate(string $modelClass, MutationInterface $mutation): void
    {
        $model = $modelClass::where($this->syncKeyColumn, $mutation->getEntityId())->first();

        if ($model === null) {
            throw new EntityNotFoundException(class_basename($modelClass), $mutation->getEntityId());
        }

        $model->fill($this->filterPayload($model, $mutation->getPayload()));
        $this->saveOrThrow($model, $mutation->getMutationId());
    }

    private function applyDelete(string $modelClass, MutationInterface $mutation): void
    {
        $model = $modelClass::where($this->syncKeyColumn, $mutation->getEntityId())->first();

        if ($model === null) {
            throw new EntityNotFoundException(class_basename($modelClass), $mutation->getEntityId());
        }

        $model->delete();
    }

    /**
     * Filter an incoming payload through the model's syncable field list (if any).
     *
     * If the model defines getSyncableFields() and it returns a non-null array,
     * only those keys are kept. Otherwise the full payload is returned unchanged and
     * Laravel's $fillable acts as the sole guard.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function filterPayload(Model $model, array $payload): array
    {
        if (! method_exists($model, 'getSyncableFields')) {
            return $payload;
        }

        $fields = $model->getSyncableFields();

        if ($fields === null) {
            return $payload;
        }

        return array_intersect_key($payload, array_flip($fields));
    }

    /**
     * Save a model and convert any Laravel ValidationException into a
     * MutationValidationException so callers get structured error data.
     */
    private function saveOrThrow(Model $model, string $mutationId): void
    {
        try {
            $model->save();
        } catch (ValidationException $e) {
            throw new MutationValidationException(
                messages: $e->validator->errors()->toArray(),
                mutationId: $mutationId,
            );
        }
    }
}
