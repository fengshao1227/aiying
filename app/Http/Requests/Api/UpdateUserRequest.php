<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:50',
            'avatar' => 'nullable|string|max:255',
            'gender' => 'nullable|integer|in:0,1,2',
            'phone' => 'nullable|string|regex:/^1[3-9]\d{9}$/',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => '手机号格式不正确',
            'gender.in' => '性别值不正确',
        ];
    }
}
