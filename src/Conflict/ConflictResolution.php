<?php

namespace Tether\Core\Conflict;

/**
 * Represents the outcome of a conflict resolution decision.
 *
 * A resolver callable returns one of:
 *   - ConflictResolution::apply($mergedPayload)  - apply the given payload
 *   - ConflictResolution::reject()               - discard the incoming mutation
 */
final class ConflictResolution
{
    private function __construct(
        public readonly bool $shouldApply,
        /** @var array<string, mixed> */
        public readonly array $payload = [],
    ) {}

    /**
     * Apply the mutation, optionally with a transformed payload.
     * Pass the original payload to apply as-is, or a modified one to merge.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function apply(array $payload): self
    {
        return new self(shouldApply: true, payload: $payload);
    }

    /**
     * Reject the incoming mutation. Server state wins.
     */
    public static function reject(): self
    {
        return new self(shouldApply: false);
    }
}
