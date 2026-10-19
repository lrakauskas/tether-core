<?php

namespace Tests\Models;

use Illuminate\Support\Facades\Validator;

class ValidatingPost extends TestPost
{
    public function save(array $options = [])
    {
        Validator::make($this->getAttributes(), ['title' => 'required'])->validate();

        return parent::save($options);
    }
}
