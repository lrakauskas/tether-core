<?php

namespace Tether\Core\Exceptions;

use RuntimeException;

/**
 * Thrown when an update or delete mutation targets an entity that does not
 * exist in the local database. Carries the model class and entity ID so the
 * server can return structured rejection data to the client.
 */
class EntityNotFoundException extends RuntimeException
{
    public function __construct(
        public readonly string $model,
        public readonly string $entityId,
    ) {
        parent::__construct("Entity [{$model}:{$entityId}] not found.");
    }
}
