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

        // 转换数据格式，使用product_id作为id返回给前端
        $transformedData = $packages->getCollection()->map(function ($package) {
            return [
                'id' => $package->product_id ?? $package->id, // 使用product_id，如果没有则用套餐id
                'package_id' => $package->id, // 保留原始套餐id供参考
                'name' => $package->name,
                'price' => $package->price,
                'cover_image' => $package->cover_image,
                'description' => $package->description,
                'services' => $package->services,
                'duration_days' => $package->duration_days,
            ];
        });

        $packages->setCollection($transformedData);

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

        // 返回格式与列表保持一致
        $data = [
            'id' => $package->product_id ?? $package->id,
            'package_id' => $package->id,
            'name' => $package->name,
            'price' => $package->price,
            'cover_image' => $package->cover_image,
            'description' => $package->description,
            'services' => $package->services,
            'duration_days' => $package->duration_days,
        ];

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $data,
        ]);
    }
}
