<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\Category;
use App\Models\V2\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * 获取商品分类列表
     * GET /v2/products/categories
     */
    public function categories(): JsonResponse
    {
        $categories = Category::enabled()
            ->ordered()
            ->get(['id', 'name', 'icon', 'sort_order']);

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => $categories,
        ]);
    }

    /**
     * 获取商品列表
     * GET /v2/products
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category:id,name')
            ->onSale()
            ->ordered();

        // 按分类筛选
        if ($request->has('category_id')) {
            $query->inCategory($request->category_id);
        }

        // 按配送类型筛选
        if ($request->has('delivery_type')) {
            $query->byDeliveryType($request->delivery_type);
        }

        // 分页
        $perPage = $request->input('per_page', 20);
        $products = $query->paginate($perPage);

        // 格式化数据
        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'category_id' => $product->category_id,
                'name' => $product->name,
                'cover_image' => $product->cover_image,
                'price' => $product->price,
                'original_price' => $product->original_price,
                'points_price' => $product->points_price,
                'stock' => $product->stock,
                'unit' => $product->unit,
                'delivery_type' => $product->delivery_type,
                'category' => [
                    'id' => $product->category->id ?? null,
                    'name' => $product->category->name ?? null,
                ],
            ];
        });

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => [
                'list' => $products->items(),
                'total' => $products->total(),
                'currentPage' => $products->currentPage(),
                'perPage' => $products->perPage(),
                'lastPage' => $products->lastPage(),
            ],
        ]);
    }

    /**
     * 获取商品详情
     * GET /v2/products/{id}
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with('category:id,name')
            ->find($id);

        if (!$product) {
            return response()->json([
                'code' => 404,
                'message' => '商品不存在',
                'data' => null,
            ], 404);
        }

        if ($product->status !== Product::STATUS_ON) {
            return response()->json([
                'code' => 400,
                'message' => '商品已下架',
                'data' => null,
            ], 400);
        }

        $data = [
            'id' => $product->id,
            'category_id' => $product->category_id,
            'name' => $product->name,
            'cover_image' => $product->cover_image,
            'images' => $product->images ?? [],
            'delivery_type' => $product->delivery_type,
            'original_price' => $product->original_price,
            'price' => $product->price,
            'points_price' => $product->points_price,
            'stock' => $product->stock,
            'unit' => $product->unit,
            'summary' => $product->summary,
            'description' => $product->description,
            'status' => $product->status,
            'category' => [
                'id' => $product->category->id ?? null,
                'name' => $product->category->name ?? null,
            ],
        ];

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => $data,
        ]);
    }
}
