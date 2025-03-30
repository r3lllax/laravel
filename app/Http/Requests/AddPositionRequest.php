<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddPositionRequest extends APIRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'menu_id'=>'required|integer|exists:menus,id',
            'count'=>'required|integer',
        ];
    }
}
