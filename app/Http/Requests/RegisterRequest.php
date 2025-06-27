<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'phone'    => 'required|unique:users',
            'password' => 'required|min:6',
            'role'     => 'required|exists:roles,name',
            'dob'      => 'nullable|date',
            'gender'   => 'nullable|in:male,female,other',
            'language' => 'nullable|string|max:5',
            'location' => 'nullable|string|max:255',
            'address'      => 'required|string|max:255',
            'city'         => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:100',
            'country'      => 'nullable|string|max:100',
            'postal_code'  => 'nullable|string|max:20',
            'address_title'=> 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email is required.',
            'email.unique' => 'Email already exists.',
            'phone.unique' => 'Phone number is already taken.',
            'role.exists' => 'Invalid role specified.',
        ];
    }
}
