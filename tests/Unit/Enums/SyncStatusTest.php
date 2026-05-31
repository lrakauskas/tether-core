<?php

use Tether\Core\Enums\SyncStatus;

it('has the correct values', function () {
    expect(SyncStatus::Pending->value)->toBe('pending')
        ->and(SyncStatus::Synced->value)->toBe('synced')
        ->and(SyncStatus::Failed->value)->toBe('failed');
});

it('can be instantiated from a string value', function () {
    expect(SyncStatus::from('pending'))->toBe(SyncStatus::Pending)
        ->and(SyncStatus::from('synced'))->toBe(SyncStatus::Synced)
        ->and(SyncStatus::from('failed'))->toBe(SyncStatus::Failed);
});

it('throws on invalid value', function () {
    SyncStatus::from('unknown');
})->throws(\ValueError::class);
