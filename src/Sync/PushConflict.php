<?php

namespace Tether\Core\Sync;

final readonly class PushConflict
{
    /**
     * @param array{server_state?: array<string, mixed>, server_updated_at?: string|null}|array<string, mixed> $data
     */
    public function __construct(
        public string $mutationId,
        public string $reason = 'conflict',
        public array $data = [],
    ) {}

    /** @return array<string, mixed> */
    public function serverState(): array
    {
        return $this->data['server_state'] ?? [];
    }

    /**
     * @return array{mutation_id: string, reason: string, data: array<string, mixed>}
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
     * @param array{mutation_id: string, reason?: string, data?: array<string, mixed>} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            mutationId: $data['mutation_id'],
            reason: $data['reason'] ?? 'conflict',
            data: $data['data'] ?? [],
        );
    }
}
