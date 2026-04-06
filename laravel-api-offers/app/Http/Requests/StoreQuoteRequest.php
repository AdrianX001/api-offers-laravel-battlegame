<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'client_name'  => 'required|string|max:255',
            'car_plate'    => 'required|string|max:32',
            'insurer_name' => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
        ];
    }
}
