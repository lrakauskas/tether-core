<?php

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('registers nullable unique ULID columns and can drop them', function (string $column) {
    Schema::create('identities', function (Blueprint $table) use ($column) {
        $table->id();
        $definition = $column === 'tether_id' ? $table->tetherUlid() : $table->tetherUlid($column);
        expect($definition->type)->toBe('char')
            ->and($definition->length)->toBe(26);
    });

    expect(Schema::hasColumn('identities', $column))->toBeTrue();
    DB::table('identities')->insert([[$column => null], [$column => null]]);
    DB::table('identities')->insert([$column => '01HXYZ0001AABBCCDD0011AABB']);
    expect(fn () => DB::table('identities')->insert([$column => '01HXYZ0001AABBCCDD0011AABB']))
        ->toThrow(QueryException::class);

    // SQLite requires the index to be removed before its column is dropped.
    Schema::table('identities', fn (Blueprint $table) => $table->dropUnique([$column]));
    Schema::table('identities', function (Blueprint $table) use ($column) {
        $column === 'tether_id' ? $table->dropTetherUlid() : $table->dropTetherUlid($column);
    });

    expect(Schema::hasColumn('identities', $column))->toBeFalse()
        ->and(DB::table('identities')->count())->toBe(3);
})->with(['tether_id', 'sync_id']);
