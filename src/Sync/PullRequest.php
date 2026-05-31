<?php

namespace Tether\Core\Sync;

final readonly class PullRequest
{
    public function __construct(
        public string $clientId,
        public ?int $cursor = null,
        public ?int $limit = null,
    ) {}

    /**
     * @return array{client_id: string, last_sync_cursor: int|null, limit?: int}
     */
    public function toArray(): array
    {
        $data = [
            'client_id' => $this->clientId,
            'last_sync_cursor' => $this->cursor,
        ];

        if ($this->limit !== null) {
            $data['limit'] = $this->limit;
        }

        return $data;
    }

    /**
     * @param array{client_id: string, last_sync_cursor?: int|null, limit?: int|null} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            clientId: $data['client_id'],
            cursor: isset($data['last_sync_cursor']) ? (int) $data['last_sync_cursor'] : null,
            limit: isset($data['limit']) ? (int) $data['limit'] : null,
        );
    }
}
