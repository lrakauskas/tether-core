<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class SoftDeletedPost extends TestPost
{
    use SoftDeletes;
}
