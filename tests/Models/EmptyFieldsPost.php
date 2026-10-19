<?php

namespace Tests\Models;

class EmptyFieldsPost extends TestPost
{
    public function getSyncableFields(): array
    {
        return [];
    }
}
