<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateOrderRequest extends FormRequest
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
            'ticket_number' => 'required|digits|between:0,99',
            'status' => 'required|in:p,r,d,c',
            'customer_id' => 'exists:customers,id',
            'total_price' => 'required|numeric|between:0,99.99',
            'total_paid' => 'required|numeric|between:0,99.99',
            'total_paid_with_points' => 'required|numeric|between:0,99.99',
            'points_gained' => 'required|digits',
            'points_used_to_pay' => 'required|digits',
            'payment_type' => 'required|in:visa,paypal,mbway',
            'payment_reference' => 'required',
            'date' => 'required|date|after:today',
            'delivered_by' => 'exists:users,id',
            'custom' => 'nullable'
        ];
    }
}
