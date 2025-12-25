<?php

namespace App\Http\Controllers\Admin\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('products');

        if ($request->filled('keyword')) {
            $keyword = addcslashes($request->keyword, '%_');
            $query->where('name', 'like', "%{$keyword}%");
        }

        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $categories = $query->ordered()->paginate($perPage);

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $categories,
        ]);
    }

    public function show($id)
    {
        $category = Category::withCount('products')->find($id);

        if (!$category) {
            return response()->json(['code' => 404, 'message' => '分类不存在'], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $category,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'message' => $validator->errors()->first()], 400);
        }

        $category = Category::create([
            'name' => $request->name,
            'icon' => $request->icon,
            'sort_order' => $request->sort_order ?? 0,
            'status' => $request->status ?? Category::STATUS_ENABLED,
        ]);

        return response()->json([
            'code' => 200,
            'message' => '创建成功',
            'data' => $category,
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['code' => 404, 'message' => '分类不存在'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'icon' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'message' => $validator->errors()->first()], 400);
        }

        $category->update($request->only(['name', 'icon', 'sort_order', 'status']));

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $category,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['code' => 404, 'message' => '分类不存在'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'message' => $validator->errors()->first()], 400);
        }

        $category->status = (int) $request->status;
        $category->save();

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $category,
        ]);
    }

    public function destroy($id)
    {
        $category = Category::withCount('products')->find($id);

        if (!$category) {
            return response()->json(['code' => 404, 'message' => '分类不存在'], 404);
        }

        if ($category->products_count > 0) {
            return response()->json(['code' => 400, 'message' => '该分类下有商品，无法删除'], 400);
        }

        $category->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
        ]);
    }
}
