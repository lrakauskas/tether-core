<?php

namespace Tests\Models;

class RestrictedPost extends TestPost
{
    public function getSyncableFields(): array
    {
        return ['title'];
    }
}
