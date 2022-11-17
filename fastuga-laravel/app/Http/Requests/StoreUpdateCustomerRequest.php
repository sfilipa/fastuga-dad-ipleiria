<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateCustomerRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'phone' => 'required|digits:9',
            'points' => 'required|numeric',
            'nif' => 'required|digits:9',
            'default_payment_type' => 'required|in:visa,paypal,mbway',
            'default_payment_reference' => 'required',
            'custom' => 'nullable'
        ];
    }
}
