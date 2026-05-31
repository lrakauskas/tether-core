<?php

namespace Tether\Core\Identity;

use Illuminate\Support\Str;

class UlidGenerator
{
    /**
     * Generate a new ULID string.
     */
    public function generate(): string
    {
        return (string) Str::ulid();
    }
}
