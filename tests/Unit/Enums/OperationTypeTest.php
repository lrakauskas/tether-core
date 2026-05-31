<?php

use Tether\Core\Enums\OperationType;

it('has the correct values', function () {
    expect(OperationType::Create->value)->toBe('create')
        ->and(OperationType::Update->value)->toBe('update')
        ->and(OperationType::Delete->value)->toBe('delete');
});

it('can be instantiated from a string value', function () {
    expect(OperationType::from('create'))->toBe(OperationType::Create)
        ->and(OperationType::from('update'))->toBe(OperationType::Update)
        ->and(OperationType::from('delete'))->toBe(OperationType::Delete);
});

it('throws on invalid value', function () {
    OperationType::from('invalid');
})->throws(\ValueError::class);
