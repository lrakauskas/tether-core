<?php

namespace Tether\Core\Sync;

final readonly class PushRejection
{
    /**
     * @param array<string, mixed>|object $data
     */
    public function __construct(
        public string $mutationId,
        public string $reason,
        public array|object $data = [],
    ) {}

    /**
     * @return array{mutation_id: string, reason: string, data: array<string, mixed>|object}
     */
    public function toArray(): array
    {
        return [
            'mutation_id' => $this->mutationId,
            'reason' => $this->reason,
            'data' => $this->data,
        ];
    }

    /**
     * @param array{mutation_id: string, reason: string, data?: array<string, mixed>|object} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            mutationId: $data['mutation_id'],
            reason: $data['reason'],
            data: $data['data'] ?? [],
        );
    }
}
