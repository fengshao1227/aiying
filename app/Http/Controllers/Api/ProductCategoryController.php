<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    /**
     * 获取分类列表
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductCategory::query()
            ->where('status', 1)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc');

        // 如果需要分页
        if ($request->has('page')) {
            $categories = $query->paginate($request->input('per_page', 15));
        } else {
            $categories = $query->get();
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $categories,
        ]);
    }

    /**
     * 获取分类详情
     */
    public function show($id): JsonResponse
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return response()->json([
                'code' => 404,
                'message' => '分类不存在',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $category,
        ]);
    }
}
