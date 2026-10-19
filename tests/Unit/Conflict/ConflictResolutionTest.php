<?php

use Tether\Core\Conflict\ConflictResolution;

it('preserves a resolved payload when accepting a mutation', function () {
    $payload = ['title' => 'Merged', 'metadata' => ['tags' => ['offline']], 'body' => null];
    $resolution = ConflictResolution::apply($payload);

    expect($resolution->shouldApply)->toBeTrue()
        ->and($resolution->payload)->toBe($payload);
});

it('rejects mutations without carrying an apply payload', function () {
    $resolution = ConflictResolution::reject();

    expect($resolution->shouldApply)->toBeFalse()
        ->and($resolution->payload)->toBe([]);
});
