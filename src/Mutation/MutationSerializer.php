<?php

namespace Tether\Core\Mutation;

use Tether\Core\Contracts\MutationInterface;
class MutationSerializer
{
    /**
     * Serialize a Mutation to a plain array (suitable for JSON encoding or DB storage).
     *
     * @return array{
     *     mutation_id: string,
     *     entity_id: string,
     *     model: string,
     *     operation: string,
     *     payload: array<string, mixed>,
     *     version: int,
     *     timestamp: int,
     * }
     */
    public function toArray(MutationInterface $mutation): array
    {
        if ($mutation instanceof Mutation) {
            return $mutation->toArray();
        }

        return (new Mutation(
            mutationId: $mutation->getMutationId(),
            entityId: $mutation->getEntityId(),
            model: $mutation->getModel(),
            operation: $mutation->getOperation(),
            payload: $mutation->getPayload(),
            version: $mutation->getVersion(),
            timestamp: $mutation->getTimestamp(),
        ))->toArray();
    }

    /**
     * Deserialize a plain array back into a Mutation value object.
     *
     * @param array{
     *     mutation_id: string,
     *     entity_id: string,
     *     model: string,
     *     operation: string,
     *     payload: array<string, mixed>,
     *     version: int,
     *     timestamp: int,
     * } $data
     */
    public function fromArray(array $data): Mutation
    {
        return Mutation::fromArray($data);
    }
}
