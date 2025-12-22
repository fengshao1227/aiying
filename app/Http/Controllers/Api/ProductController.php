<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * 获取商品列表
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'images', 'specifications'])
            ->where('status', 1);

        // 分类筛选
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 搜索
        if ($request->has('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        // 排序
        $sortBy = $request->input('sort_by', 'sort_order');
        $sortOrder = $request->input('sort_order', 'asc');

        if (in_array($sortBy, ['sort_order', 'price', 'sales', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $products = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $products,
        ]);
    }

    /**
     * 获取商品详情
     */
    public function show($id): JsonResponse
    {
        $product = Product::with(['category', 'images', 'specifications'])
            ->find($id);

        if (!$product) {
            return response()->json([
                'code' => 404,
                'message' => '商品不存在',
                'data' => null,
            ], 404);
        }

        if ($product->status !== 1) {
            return response()->json([
                'code' => 400,
                'message' => '商品已下架',
                'data' => null,
            ], 400);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $product,
        ]);
    }

    /**
     * 获取热销商品
     */
    public function hot(Request $request): JsonResponse
    {
        $products = Product::with(['category', 'images'])
            ->where('status', 1)
            ->orderBy('sales', 'desc')
            ->limit($request->input('limit', 10))
            ->get();

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $products,
        ]);
    }
}
