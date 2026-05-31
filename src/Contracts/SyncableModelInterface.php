<?php

namespace Tether\Core\Contracts;

interface SyncableModelInterface
{
    /**
     * Returns the list of field names that should be included in sync payloads.
     *
     * @return string[]
     */
    public function getSyncableFields(): array;

    /**
     * Returns the unique entity identifier (ULID) for this model.
     */
    public function getEntityId(): string;
}
