<?php

use Illuminate\Support\Str;
use Tether\Core\Identity\UlidGenerator;

it('generates a valid ULID string', function () {
    $generator = new UlidGenerator();
    $ulid = $generator->generate();

    expect($ulid)->toBeString()
        ->toHaveLength(26)
        ->toMatch('/^[0-9A-HJKMNP-TV-Z]{26}$/');
});

it('generates unique values on each call', function () {
    $generator = new UlidGenerator();

    $a = $generator->generate();
    $b = $generator->generate();

    expect($a)->not->toBe($b);
});

it('generates ULIDs that are lexicographically sortable', function () {
    $generator = new UlidGenerator();

    $first = $generator->generate();
    usleep(1000); // ensure different millisecond
    $second = $generator->generate();

    expect(strcmp($first, $second))->toBeLessThan(0);
});
