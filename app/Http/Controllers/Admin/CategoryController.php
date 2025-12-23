<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * 获取分类列表（树形结构）
     */
    public function index()
    {
        $categories = ProductCategory::orderBy('sort_order')->get();

        // 构建树形结构
        $tree = $this->buildTree($categories);

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $tree,
        ]);
    }

    /**
     * 构建树形结构
     */
    private function buildTree($categories, $parentId = null)
    {
        $result = [];
        foreach ($categories as $category) {
            if ($category->parent_id === $parentId) {
                $children = $this->buildTree($categories, $category->id);
                $item = $category->toArray();
                if (count($children) > 0) {
                    $item['children'] = $children;
                }
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * 获取分类详情
     */
    public function show($id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return response()->json([
                'code' => 404,
                'message' => '分类不存在',
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $category,
        ]);
    }

    /**
     * 创建分类
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:product_categories,id',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $category = ProductCategory::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'icon' => $request->icon,
            'sort_order' => $request->sort_order ?? 0,
            'status' => $request->status ?? 1,
        ]);

        return response()->json([
            'code' => 200,
            'message' => '创建成功',
            'data' => $category,
        ]);
    }

    /**
     * 更新分类
     */
    public function update(Request $request, $id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return response()->json([
                'code' => 404,
                'message' => '分类不存在',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'parent_id' => 'nullable|integer|exists:product_categories,id',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        // 防止将分类设置为自己的子分类
        if ($request->parent_id == $id) {
            return response()->json([
                'code' => 400,
                'message' => '不能将分类设置为自己的子分类',
            ], 400);
        }

        $category->update($request->only(['name', 'parent_id', 'icon', 'sort_order', 'status']));

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $category,
        ]);
    }

    /**
     * 删除分类（软删除）
     */
    public function destroy($id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return response()->json([
                'code' => 404,
                'message' => '分类不存在',
            ], 404);
        }

        $category->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
        ]);
    }
}
