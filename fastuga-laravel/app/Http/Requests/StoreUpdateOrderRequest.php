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
            'ticket_number' => 'required|numeric|between:0,99',
            'status' => 'required|in:P,R,D,C',
            'customer_id' => 'nullable|exists:customers,id',
            'total_price' => 'required|numeric|between:0,99.99',
            'total_paid' => 'required|numeric|between:0,99.99',
            'total_paid_with_points' => 'required|numeric|between:0,99.99',
            'points_gained' => 'required|numeric|min:0',
            'points_used_to_pay' => 'required|numeric|min:0',
            'payment_type' => 'nullable|in:VISA,PAYPAL,MBWAY',
            'payment_reference' => 'required_if:payment_type:exists,null',
            'date' => 'required|date',
            'delivered_by' => 'nullable|exists:users,id',
            'custom' => 'nullable'
        ];
    }
}
