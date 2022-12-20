<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UpdatePasswordRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'current_password' => 'required',
            'password' => 'required|min:8|different:current_password',
            'confirm_password' => 'required|same:password',
        ];
    }

     public function messages()
        {
            return [
                'current_password.required' => 'Current Password cannot be empty',
                'password.required' => 'New Password cannot be empty',
                'password.min' => 'New Password too short at least 8 characters',
                'password.different' => 'New Password and Current Password are the same',
                'confirm_password.required' => 'Confirmation Password cannot be empty',
                'confirm_password.same' => 'Confirmation Password and New Password must match'
            ];
        }
}
