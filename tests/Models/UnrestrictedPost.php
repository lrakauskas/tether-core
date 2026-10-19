<?php

namespace Tests\Models;

class UnrestrictedPost extends TestPost
{
    public function getSyncableFields(): ?array
    {
        return null;
    }
}
