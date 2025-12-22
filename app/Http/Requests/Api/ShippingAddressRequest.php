<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ShippingAddressRequest extends FormRequest
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
            'receiver_phone' => 'required|string|regex:/^1[3-9]\d{9}$/',
            'detail_address' => 'required|string|max:200',
            'receiver_name' => 'nullable|string|max:50',
            'province' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:50',
            'district' => 'nullable|string|max:50',
            'is_default' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'receiver_phone.required' => '收货人电话不能为空',
            'receiver_phone.regex' => '手机号格式不正确',
            'detail_address.required' => '详细地址不能为空',
        ];
    }
}
