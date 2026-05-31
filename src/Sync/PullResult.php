<?php

namespace Tether\Core\Sync;

final readonly class PullResult
{
    /**
     * @param list<Snapshot> $snapshots
     */
    public function __construct(
        public array $snapshots = [],
        public ?int $newSyncCursor = null,
        public bool $hasMore = false,
    ) {}

    /**
     * @return array{snapshots: list<array<string, mixed>>, new_sync_cursor: int|null, has_more: bool}
     */
    public function toArray(): array
    {
        return [
            'snapshots' => array_map(fn (Snapshot $snapshot): array => $snapshot->toArray(), $this->snapshots),
            'new_sync_cursor' => $this->newSyncCursor,
            'has_more' => $this->hasMore,
        ];
    }

    /**
     * @param array{snapshots?: list<array<string, mixed>>, new_sync_cursor?: int|null, has_more?: bool} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            snapshots: array_map(fn (array $snapshot): Snapshot => Snapshot::fromArray($snapshot), $data['snapshots'] ?? []),
            newSyncCursor: isset($data['new_sync_cursor']) ? (int) $data['new_sync_cursor'] : null,
            hasMore: (bool) ($data['has_more'] ?? false),
        );
    }
}
