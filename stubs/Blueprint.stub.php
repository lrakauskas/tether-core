<?php

// This file is not loaded at runtime. It exists solely to provide IDE autocompletion
// for Blueprint macros registered by TetherCoreServiceProvider.

namespace Illuminate\Database\Schema;

class Blueprint
{
    /**
     * Add a Tether sync identity column (char 26, unique, nullable).
     *
     * Registered by tether/core. Use in any migration that participates in Tether sync,
     * on both client and server applications.
     *
     * @param  string  $column  Column name (default: tether_id)
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function tetherUlid(string $column = 'tether_id'): ColumnDefinition {}

    /**
     * Drop the Tether sync identity column.
     *
     * @param  string  $column  Column name (default: tether_id)
     */
    public function dropTetherUlid(string $column = 'tether_id'): void {}
}
