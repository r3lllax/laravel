<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddRequest extends APIRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'work_shift_id'=>'required|integer|exists:work_shifts,id',
            'table_id'=>'required|integer|exists:tables,id',
            'number_of_person'=>'integer',
        ];
    }
}
