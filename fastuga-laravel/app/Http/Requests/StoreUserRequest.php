<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'type' => 'required|in:C,EC,ED,EM',
            'blocked' => 'required|in:0,1',
            'photo_url' => 'sometimes',
            'custom' => 'nullable'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name field cannot be empty',
            'name.min' => 'Name too short',
            'email.required' => 'Email filed cannot be empty',
            'email.email' => 'Wrong email format',
            'email.unique' => 'Email already exists',
            'password.required' => 'Password field cannot be empty',
            'password.min' => 'Password too short at least 8 characters'
    ];
    }
}
