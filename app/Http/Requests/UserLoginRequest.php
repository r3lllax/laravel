<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class   UserLoginRequest extends APIRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'login'=>'required|string',
            'password'=>'required|string'
        ];
    }
}
