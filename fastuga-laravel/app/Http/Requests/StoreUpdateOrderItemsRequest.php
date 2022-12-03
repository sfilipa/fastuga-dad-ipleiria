<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateOrderItemsRequest extends FormRequest
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
            'order_id' => 'required|exists:orders,id',
            'order_local_number' => 'required|integer|min:0, max:99',
            'product_id' => 'required|exists:products,id',
            'status' => 'required|in:W,P,R',
            'price' => 'required|numeric|between:0,99.99',
            'preparation_by' => 'nullable|exists:users,id',
            'notes' => 'nullable',
            'custom' => 'nullable'
        ];
    }
}
