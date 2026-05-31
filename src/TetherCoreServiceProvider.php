<?php

namespace Tether\Core;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

class TetherCoreServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // tetherUlid() - adds a char(26) ULID column used as the Tether sync identity.
        // Available in both client and server migrations.
        //
        // Usage:
        //   $table->tetherUlid();              // column: tether_id
        //   $table->tetherUlid('sync_id');     // custom column name
        Blueprint::macro('tetherUlid', function (string $column = 'tether_id') {
            /** @var Blueprint $this */
            return $this->char($column, 26)->unique()->nullable();
        });

        Blueprint::macro('dropTetherUlid', function (string $column = 'tether_id') {
            /** @var Blueprint $this */
            return $this->dropColumn($column);
        });
    }
}
