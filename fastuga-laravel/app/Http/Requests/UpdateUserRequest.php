<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
            'name' => 'required|min:3',
            'email' => ['required','email', Rule::unique('users')->ignore($this->user)],
            'type' => 'required|in:C,EC,ED,EM',
            'blocked' => 'required|in:0,1',
            'photo_url' => 'nullable',
            'custom' => 'nullable'
        ];
    }
}
