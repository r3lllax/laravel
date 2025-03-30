<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkShiftRequest extends APIRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start'=>'required|date|date_format:Y-m-d H:i',
            'end'=>'required|date|date_format:Y-m-d H:i|after:start',
        ];
    }
}
