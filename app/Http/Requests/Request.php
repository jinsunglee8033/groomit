<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    public function sanitize()
    {
        $input = $this->all();

        $input['note'] = filter_var($input['note'], FILTER_SANITIZE_STRING);

        $this->replace($input);
    }
}
