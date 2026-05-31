<?php

namespace Tether\Core\Enums;

enum OperationType: string
{
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
}
