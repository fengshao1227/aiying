<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddToCartRequest;
use App\Http\Requests\Api\UpdateCartRequest;
use App\Models\ShoppingCart;
use App\Models\Product;
use App\Models\ProductSpecification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShoppingCartController extends Controller
{
    /**
     * 获取购物车列表
     */
    public function index(Request $request): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        $cartItems = ShoppingCart::with([
            'product' => function ($query) {
                $query->with(['category', 'images']);
            },
            'specification'
        ])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // 转换为前端期望的格式
        $list = $cartItems->map(function ($item) {
            // 使用商品实时价格，不使用购物车缓存价格
            $price = $item->specification_id
                ? ($item->specification->price ?? 0)
                : ($item->product->price ?? 0);

            return [
                'cart_id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name ?? '',
                'price' => $price,
                'image_url' => $item->product->cover_image ?? '',
                'quantity' => $item->quantity,
                'specification_id' => $item->specification_id,
                'specification_name' => $item->specification ? json_encode($item->specification->spec_values) : null,
            ];
        });

        // 计算总价
        $totalAmount = $list->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => [
                'list' => $list,
                'total_amount' => $totalAmount,
                'total_items' => $list->count(),
            ],
        ]);
    }

    /**
     * 添加到购物车
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        // 验证商品
        $product = Product::find($request->product_id);
        if (!$product || $product->status !== 1) {
            return response()->json([
                'code' => 400,
                'message' => '商品不存在或已下架',
                'data' => null,
            ], 400);
        }

        // 验证规格
        $specification = null;
        if ($request->specification_id) {
            $specification = ProductSpecification::where('product_id', $request->product_id)
                ->where('id', $request->specification_id)
                ->first();

            if (!$specification) {
                return response()->json([
                    'code' => 400,
                    'message' => '商品规格不存在',
                    'data' => null,
                ], 400);
            }

            // 检查库存
            if ($specification->stock < $request->quantity) {
                return response()->json([
                    'code' => 400,
                    'message' => '库存不足',
                    'data' => null,
                ], 400);
            }

            $price = $specification->price;
        } else {
            // 检查商品库存
            if ($product->stock < $request->quantity) {
                return response()->json([
                    'code' => 400,
                    'message' => '库存不足',
                    'data' => null,
                ], 400);
            }

            $price = $product->price;
        }

        // 检查购物车是否已有相同商品和规格
        $existingCart = ShoppingCart::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->where('specification_id', $request->specification_id)
            ->first();

        if ($existingCart) {
            // 更新数量（不再保存价格，使用商品实时价格）
            $existingCart->update([
                'quantity' => $existingCart->quantity + $request->quantity,
            ]);

            return response()->json([
                'code' => 200,
                'message' => '已更新购物车数量',
                'data' => $existingCart,
            ]);
        }

        // 创建购物车记录（不保存价格，使用商品实时价格）
        $cartItem = ShoppingCart::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
            'specification_id' => $request->specification_id,
            'quantity' => $request->quantity,
            // 移除 'price' => $price, 使用商品实时价格
        ]);

        return response()->json([
            'code' => 200,
            'message' => '添加成功',
            'data' => $cartItem,
        ]);
    }

    /**
     * 更新购物车数量
     */
    public function update(UpdateCartRequest $request, $id): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        $cartItem = ShoppingCart::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'code' => 404,
                'message' => '购物车记录不存在',
                'data' => null,
            ], 404);
        }

        // 检查库存
        if ($cartItem->specification_id) {
            $specification = ProductSpecification::find($cartItem->specification_id);
            if ($specification->stock < $request->quantity) {
                return response()->json([
                    'code' => 400,
                    'message' => '库存不足',
                    'data' => null,
                ], 400);
            }
        } else {
            $product = Product::find($cartItem->product_id);
            if ($product->stock < $request->quantity) {
                return response()->json([
                    'code' => 400,
                    'message' => '库存不足',
                    'data' => null,
                ], 400);
            }
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $cartItem,
        ]);
    }

    /**
     * 删除购物车记录
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        $cartItem = ShoppingCart::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'code' => 404,
                'message' => '购物车记录不存在',
                'data' => null,
            ], 404);
        }

        $cartItem->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
            'data' => null,
        ]);
    }

    /**
     * 清空购物车
     */
    public function clear(Request $request): JsonResponse
    {
        $user = User::where('openid', $request->header('X-Openid'))->first();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '未登录',
                'data' => null,
            ], 401);
        }

        ShoppingCart::where('user_id', $user->id)->delete();

        return response()->json([
            'code' => 200,
            'message' => '清空成功',
            'data' => null,
        ]);
    }
}
