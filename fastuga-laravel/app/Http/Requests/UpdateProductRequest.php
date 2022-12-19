<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'type' => 'required|in:hot dish,cold dish,drink,dessert',
            'description' => 'required',
            'photo_url' => 'sometimes',
            'price' => 'required|numeric|between:0.1,99.99',
            'custom' => 'nullable'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Fill in the name',
            'type.required' => 'Select a type',
            'type.in:hot dish,cold dish,drink,dessert' => 'Type must be hot dish, cold dish, drink or dessert',
            'description.required' => 'Type a description',
            'price.required' => 'Enter a price',
            'price.numeric' => 'The price has to be a number',
            'price.between:0.1,99.99' => 'The price must be between 0.1 - 99.99'
        ];
    }
}
