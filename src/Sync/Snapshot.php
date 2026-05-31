<?php

namespace Tether\Core\Sync;

final readonly class Snapshot
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string $entityId,
        public string $model,
        public string $operation,
        public array $payload = [],
    ) {}

    /** @param array<string, mixed> $payload */
    public function withPayload(array $payload): self
    {
        return new self($this->entityId, $this->model, $this->operation, $payload);
    }

    /**
     * @return array{entity_id: string, model: string, operation: string, payload: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'entity_id' => $this->entityId,
            'model' => $this->model,
            'operation' => $this->operation,
            'payload' => $this->payload,
        ];
    }

    /**
     * @param array{entity_id: string, model: string, operation: string, payload?: array<string, mixed>} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            entityId: $data['entity_id'],
            model: $data['model'],
            operation: $data['operation'],
            payload: $data['payload'] ?? [],
        );
    }
}
