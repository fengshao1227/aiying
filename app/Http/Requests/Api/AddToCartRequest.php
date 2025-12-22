<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
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
            'product_id' => 'required|integer|exists:products,id',
            'specification_id' => 'nullable|integer|exists:product_specifications,id',
            'quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => '商品ID不能为空',
            'product_id.exists' => '商品不存在',
            'specification_id.exists' => '商品规格不存在',
            'quantity.required' => '数量不能为空',
            'quantity.min' => '数量至少为1',
        ];
    }
}
