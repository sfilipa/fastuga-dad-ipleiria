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
            'order_local_number' => 'required|digits|min:0',
            'product_id' => 'required|exists:products,id',
            'status' => 'required|in:w,p,r',
            'price' => 'required|numeric|between:0,99.99',
            'preparation_by' => 'required|exists:users,id',
            'notes' => 'required',
            'custom' => 'nullable'
        ];
    }
}
