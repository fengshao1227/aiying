<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
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
            'address_id' => 'required|integer|exists:shipping_addresses,id',
            'cart_ids' => 'required|array|min:1',
            'cart_ids.*' => 'integer|exists:shopping_cart,id',
            'points_used' => 'nullable|integer|min:0',
            'shipping_fee' => 'nullable|numeric|min:0',
            'remark' => 'nullable|string|max:200',
        ];
    }

    public function messages(): array
    {
        return [
            'address_id.required' => '收货地址不能为空',
            'address_id.exists' => '收货地址不存在',
            'cart_ids.required' => '购物车商品不能为空',
            'cart_ids.min' => '至少选择一件商品',
            'points_used.min' => '积分不能为负数',
            'shipping_fee.min' => '运费不能为负数',
        ];
    }
}
