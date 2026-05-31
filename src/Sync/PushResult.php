<?php

namespace Tether\Core\Sync;

final readonly class PushResult
{
    /**
     * @param list<string> $applied
     * @param list<PushRejection> $rejected
     * @param list<PushConflict> $conflicts
     */
    public function __construct(
        public array $applied = [],
        public array $rejected = [],
        public array $conflicts = [],
    ) {}

    public function withApplied(string $mutationId): self
    {
        return new self([...$this->applied, $mutationId], $this->rejected, $this->conflicts);
    }

    public function withRejected(PushRejection $rejection): self
    {
        return new self($this->applied, [...$this->rejected, $rejection], $this->conflicts);
    }

    public function withConflict(PushConflict $conflict): self
    {
        return new self($this->applied, $this->rejected, [...$this->conflicts, $conflict]);
    }

    /**
     * @return array{applied: list<string>, rejected: list<array<string, mixed>>, conflicts: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'applied' => $this->applied,
            'rejected' => array_map(fn (PushRejection $rejection): array => $rejection->toArray(), $this->rejected),
            'conflicts' => array_map(fn (PushConflict $conflict): array => $conflict->toArray(), $this->conflicts),
        ];
    }

    /**
     * @param array{applied?: list<string>, rejected?: list<array<string, mixed>>, conflicts?: list<array<string, mixed>>} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            applied: $data['applied'] ?? [],
            rejected: array_map(fn (array $rejection): PushRejection => PushRejection::fromArray($rejection), $data['rejected'] ?? []),
            conflicts: array_map(fn (array $conflict): PushConflict => PushConflict::fromArray($conflict), $data['conflicts'] ?? []),
        );
    }
}
