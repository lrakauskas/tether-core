<?php

namespace Tether\Core\Mutation;

use Tether\Core\Contracts\MutationInterface;
use Tether\Core\Enums\OperationType;

final readonly class Mutation implements MutationInterface
{
    public function __construct(
        private string $mutationId,
        private string $entityId,
        private string $model,
        private OperationType $operation,
        /** @var array<string, mixed> */
        private array $payload,
        private int $version,
        private int $timestamp,
    ) {}

    public function getMutationId(): string
    {
        return $this->mutationId;
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getOperation(): OperationType
    {
        return $this->operation;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /** @param array<string, mixed> $payload */
    public function withPayload(array $payload): self
    {
        return new self(
            mutationId: $this->mutationId,
            entityId: $this->entityId,
            model: $this->model,
            operation: $this->operation,
            payload: $payload,
            version: $this->version,
            timestamp: $this->timestamp,
        );
    }

    /**
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
    public function toArray(): array
    {
        return [
            'mutation_id' => $this->mutationId,
            'entity_id'   => $this->entityId,
            'model'       => $this->model,
            'operation'   => $this->operation->value,
            'payload'     => $this->payload,
            'version'     => $this->version,
            'timestamp'   => $this->timestamp,
        ];
    }

    /**
     * @param array{
     *     mutation_id: string,
     *     entity_id: string,
     *     model: string,
     *     operation: string,
     *     payload?: array<string, mixed>,
     *     version: int,
     *     timestamp: int,
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            mutationId: $data['mutation_id'],
            entityId: $data['entity_id'],
            model: $data['model'],
            operation: OperationType::from($data['operation']),
            payload: $data['payload'] ?? [],
            version: (int) $data['version'],
            timestamp: (int) $data['timestamp'],
        );
    }
}
