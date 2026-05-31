<?php

namespace Tether\Core\Sync;

use Tether\Core\Mutation\Mutation;

final readonly class PushRequest
{
    /**
     * @param list<Mutation> $mutations
     */
    public function __construct(
        public string $clientId,
        public array $mutations,
    ) {}

    /**
     * @return array{client_id: string, mutations: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'client_id' => $this->clientId,
            'mutations' => array_map(fn (Mutation $mutation): array => $mutation->toArray(), $this->mutations),
        ];
    }

    /**
     * @param array{client_id: string, mutations?: list<array<string, mixed>>} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            clientId: $data['client_id'],
            mutations: array_map(fn (array $mutation): Mutation => Mutation::fromArray($mutation), $data['mutations'] ?? []),
        );
    }
}
