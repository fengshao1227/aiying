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
        $rules = [
            'order_type' => 'required|in:goods,family_meal',
            'cart_ids' => 'required|array|min:1',
            'cart_ids.*' => 'integer|exists:shopping_cart,id',
            'points_used' => 'nullable|integer|min:0',
            'shipping_fee' => 'nullable|numeric|min:0',
            'remark' => 'nullable|string|max:200',
        ];

        // 根据订单类型添加条件验证
        if ($this->input('order_type') === 'goods') {
            // 商品订单必须有收货地址
            $rules['address_id'] = 'required|integer|exists:shipping_addresses,id';
        } elseif ($this->input('order_type') === 'family_meal') {
            // 家庭套餐必须有房间号
            $rules['room_number'] = 'required|string|max:50';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'order_type.required' => '订单类型不能为空',
            'order_type.in' => '订单类型无效',
            'address_id.required' => '收货地址不能为空',
            'address_id.exists' => '收货地址不存在',
            'room_number.required' => '房间号不能为空',
            'cart_ids.required' => '购物车商品不能为空',
            'cart_ids.min' => '至少选择一件商品',
            'points_used.min' => '积分不能为负数',
            'shipping_fee.min' => '运费不能为负数',
        ];
    }
}
