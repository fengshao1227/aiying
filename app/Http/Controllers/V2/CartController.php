<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\Cart;
use App\Models\V2\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * 获取购物车列表
     * GET /v2/cart
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->attributes->get('v2_user');

        $carts = Cart::with('product.category')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // 格式化数据并检查商品有效性
        $items = $carts->map(function ($cart) {
            $isValid = $cart->isProductValid();

            return [
                'id' => $cart->id,
                'product_id' => $cart->product_id,
                'quantity' => $cart->quantity,
                'selected' => $cart->selected,
                'isValid' => $isValid,
                'product' => $cart->product ? [
                    'id' => $cart->product->id,
                    'name' => $cart->product->name,
                    'cover_image' => $cart->product->cover_image,
                    'price' => $cart->product->price,
                    'stock' => $cart->product->stock,
                    'status' => $cart->product->status,
                ] : null,
                'subtotal' => $cart->getSubtotal(),
            ];
        });

        // 计算总计（仅有效且选中的商品）
        $selectedItems = $items->filter(fn($item) => $item['selected'] && $item['isValid']);
        $totalAmount = $selectedItems->sum('subtotal');

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => [
                'items' => $items->values(),
                'totalCount' => $items->count(),
                'selectedCount' => $selectedItems->count(),
                'totalAmount' => number_format($totalAmount, 2, '.', ''),
            ],
        ]);
    }

    /**
     * 添加商品到购物车
     * POST /v2/cart
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
                'data' => null,
            ], 400);
        }

        $user = $request->attributes->get('v2_user');
        $productId = $request->product_id;
        $quantity = $request->quantity;

        // 检查商品是否存在且上架
        $product = Product::find($productId);
        if (!$product || $product->status !== Product::STATUS_ON) {
            return response()->json([
                'code' => 400,
                'message' => '商品不存在或已下架',
                'data' => null,
            ], 400);
        }

        // 检查库存
        if (!$product->hasStock($quantity)) {
            return response()->json([
                'code' => 400,
                'message' => '库存不足',
                'data' => null,
            ], 400);
        }

        // 检查是否已在购物车中
        $existingCart = Cart::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existingCart) {
            // 更新数量
            $newQuantity = $existingCart->quantity + $quantity;

            if (!$product->hasStock($newQuantity)) {
                return response()->json([
                    'code' => 400,
                    'message' => '库存不足',
                    'data' => null,
                ], 400);
            }

            $existingCart->quantity = $newQuantity;
            $existingCart->save();

            return response()->json([
                'code' => 0,
                'message' => '已更新购物车数量',
                'data' => [
                    'id' => $existingCart->id,
                    'quantity' => $existingCart->quantity,
                ],
            ]);
        }

        // 添加到购物车
        $cart = Cart::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'quantity' => $quantity,
            'selected' => true,
        ]);

        return response()->json([
            'code' => 0,
            'message' => '添加成功',
            'data' => [
                'id' => $cart->id,
                'quantity' => $cart->quantity,
            ],
        ]);
    }

    /**
     * 更新购物车商品数量
     * PUT /v2/cart/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'sometimes|integer|min:1|max:99',
            'selected' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
                'data' => null,
            ], 400);
        }

        // 至少需要一个参数
        if (!$request->has('quantity') && !$request->has('selected')) {
            return response()->json([
                'code' => 400,
                'message' => '请提供要更新的参数',
                'data' => null,
            ], 400);
        }

        $user = $request->attributes->get('v2_user');

        $cart = Cart::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$cart) {
            return response()->json([
                'code' => 404,
                'message' => '购物车项不存在',
                'data' => null,
            ], 404);
        }

        // 检查库存
        if ($request->has('quantity')) {
            $product = $cart->product;
            if (!$product || !$product->hasStock($request->quantity)) {
                return response()->json([
                    'code' => 400,
                    'message' => '库存不足',
                    'data' => null,
                ], 400);
            }
            $cart->quantity = $request->quantity;
        }

        if ($request->has('selected')) {
            $cart->selected = $request->selected;
        }

        $cart->save();

        return response()->json([
            'code' => 0,
            'message' => '更新成功',
            'data' => [
                'id' => $cart->id,
                'quantity' => $cart->quantity,
                'selected' => $cart->selected,
            ],
        ]);
    }

    /**
     * 删除购物车商品
     * DELETE /v2/cart/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('v2_user');

        $cart = Cart::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$cart) {
            return response()->json([
                'code' => 404,
                'message' => '购物车项不存在',
                'data' => null,
            ], 404);
        }

        $cart->delete();

        return response()->json([
            'code' => 0,
            'message' => '删除成功',
            'data' => null,
        ]);
    }

    /**
     * 清空购物车
     * DELETE /v2/cart
     */
    public function clear(Request $request): JsonResponse
    {
        $user = $request->attributes->get('v2_user');

        Cart::where('user_id', $user->id)->delete();

        return response()->json([
            'code' => 0,
            'message' => '购物车已清空',
            'data' => null,
        ]);
    }

    /**
     * 批量选中/取消选中
     * PUT /v2/cart/select
     */
    public function select(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'selected' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
                'data' => null,
            ], 400);
        }

        $user = $request->attributes->get('v2_user');

        Cart::where('user_id', $user->id)
            ->whereIn('id', $request->ids)
            ->update(['selected' => $request->selected]);

        return response()->json([
            'code' => 0,
            'message' => '操作成功',
            'data' => null,
        ]);
    }

    /**
     * 获取商品无效原因
     */
    private function getInvalidReason(Cart $cart): ?string
    {
        if (!$cart->product) {
            return '商品已删除';
        }

        if ($cart->product->status !== Product::STATUS_ON) {
            return '商品已下架';
        }

        if (!$cart->product->hasStock($cart->quantity)) {
            return '库存不足';
        }

        return null;
    }
}
