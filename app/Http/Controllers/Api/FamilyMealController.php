<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FamilyMealPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FamilyMealController extends Controller
{
    /**
     * 获取家庭套餐列表
     */
    public function index(Request $request): JsonResponse
    {
        $query = FamilyMealPackage::where('status', 1);

        // 搜索
        if ($request->has('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        // 排序
        $query->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc');

        $packages = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $packages,
        ]);
    }

    /**
     * 获取家庭套餐详情
     */
    public function show($id): JsonResponse
    {
        $package = FamilyMealPackage::find($id);

        if (!$package) {
            return response()->json([
                'code' => 404,
                'message' => '套餐不存在',
                'data' => null,
            ], 404);
        }

        if ($package->status !== 1) {
            return response()->json([
                'code' => 400,
                'message' => '套餐已下架',
                'data' => null,
            ], 400);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $package,
        ]);
    }
}
