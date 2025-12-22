<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FamilyMealPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{
    /**
     * 获取套餐列表
     */
    public function index(Request $request)
    {
        $query = FamilyMealPackage::with('products');

        $perPage = $request->input('per_page', 10);
        $packages = $query->latest()->paginate($perPage);

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $packages,
        ]);
    }

    /**
     * 获取套餐详情
     */
    public function show($id)
    {
        $package = FamilyMealPackage::with('products')->find($id);

        if (!$package) {
            return response()->json([
                'code' => 404,
                'message' => '套餐不存在',
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $package,
        ]);
    }

    /**
     * 创建套餐
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'price' => 'required|numeric|min:0',
            'original_price' => 'required|numeric|min:0',
            'description' => 'sometimes|string',
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $package = FamilyMealPackage::create($request->except('product_ids'));
        $package->products()->sync($request->product_ids);

        return response()->json([
            'code' => 200,
            'message' => '创建成功',
            'data' => $package->load('products'),
        ]);
    }

    /**
     * 更新套餐
     */
    public function update(Request $request, $id)
    {
        $package = FamilyMealPackage::find($id);

        if (!$package) {
            return response()->json([
                'code' => 404,
                'message' => '套餐不存在',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'original_price' => 'sometimes|numeric|min:0',
            'description' => 'sometimes|string',
            'product_ids' => 'sometimes|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $package->update($request->except('product_ids'));

        if ($request->has('product_ids')) {
            $package->products()->sync($request->product_ids);
        }

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $package->load('products'),
        ]);
    }

    /**
     * 删除套餐
     */
    public function destroy($id)
    {
        $package = FamilyMealPackage::find($id);

        if (!$package) {
            return response()->json([
                'code' => 404,
                'message' => '套餐不存在',
            ], 404);
        }

        $package->products()->detach();
        $package->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
        ]);
    }

    /**
     * 更新套餐状态
     */
    public function updateStatus(Request $request, $id)
    {
        $package = FamilyMealPackage::find($id);

        if (!$package) {
            return response()->json([
                'code' => 404,
                'message' => '套餐不存在',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $package->status = $request->status;
        $package->save();

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $package,
        ]);
    }
}
