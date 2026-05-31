<?php

namespace Tether\Core\Contracts;

use Tether\Core\Enums\OperationType;

interface MutationInterface
{
    public function getMutationId(): string;

    public function getEntityId(): string;

    public function getModel(): string;

    public function getOperation(): OperationType;

    public function getPayload(): array;

    public function getVersion(): int;

    public function getTimestamp(): int;
}
