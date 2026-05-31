<?php

namespace Tether\Core\Sync;

final readonly class SyncStatus
{
    public function __construct(
        public int $pending,
        public int $failed,
        public int $conflicts,
        public ?string $lastSyncCursor,
        public ?string $lastSyncAt,
    ) {}

    /**
     * @return array{pending: int, failed: int, conflicts: int, last_sync_cursor: string|null, last_sync_at: string|null}
     */
    public function toArray(): array
    {
        return [
            'pending' => $this->pending,
            'failed' => $this->failed,
            'conflicts' => $this->conflicts,
            'last_sync_cursor' => $this->lastSyncCursor,
            'last_sync_at' => $this->lastSyncAt,
        ];
    }
}
