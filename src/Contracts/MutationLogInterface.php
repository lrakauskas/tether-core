<?php

namespace Tether\Core\Contracts;

interface MutationLogInterface
{
    /**
     * Persist a mutation to the log.
     */
    public function record(MutationInterface $mutation): void;

    /**
     * Retrieve all pending (unsynced) mutations in insertion order.
     *
     * @return MutationInterface[]
     */
    public function pending(): array;

    /**
     * Check whether a mutation_id has already been recorded.
     */
    public function exists(string $mutationId): bool;
}
