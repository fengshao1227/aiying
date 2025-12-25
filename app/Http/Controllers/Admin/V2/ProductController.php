<?php

namespace App\Http\Controllers\Admin\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('keyword')) {
            $keyword = addcslashes($request->keyword, '%_');
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('summary', 'like', "%{$keyword}%");
            });
        }

        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('delivery_type')) {
            $query->where('delivery_type', $request->delivery_type);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $perPage = min((int) $request->input('per_page', 10), 100);
        $products = $query->ordered()->paginate($perPage);

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $products,
        ]);
    }

    public function show($id)
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return response()->json(['code' => 404, 'message' => '商品不存在'], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $product,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'cover_image' => 'nullable|string|max:500',
            'images' => 'nullable|array',
            'delivery_type' => 'required|in:express,room',
            'original_price' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'points_price' => 'nullable|integer|min:0',
            'stock' => 'required|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'summary' => 'nullable|string',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'message' => $validator->errors()->first()], 400);
        }

        $data = $request->only([
            'category_id', 'name', 'cover_image', 'images', 'delivery_type',
            'original_price', 'price', 'points_price', 'stock', 'unit',
            'summary', 'description', 'sort_order', 'status',
        ]);

        $data['status'] = $data['status'] ?? Product::STATUS_ON;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['sales'] = 0;

        $product = Product::create($data);

        return response()->json([
            'code' => 200,
            'message' => '创建成功',
            'data' => $product,
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['code' => 404, 'message' => '商品不存在'], 404);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|string|max:255',
            'cover_image' => 'nullable|string|max:500',
            'images' => 'nullable|array',
            'delivery_type' => 'sometimes|in:express,room',
            'original_price' => 'nullable|numeric|min:0',
            'price' => 'sometimes|numeric|min:0',
            'points_price' => 'nullable|integer|min:0',
            'stock' => 'sometimes|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'summary' => 'nullable|string',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'message' => $validator->errors()->first()], 400);
        }

        $product->update($request->only([
            'category_id', 'name', 'cover_image', 'images', 'delivery_type',
            'original_price', 'price', 'points_price', 'stock', 'unit',
            'summary', 'description', 'sort_order', 'status',
        ]));

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $product,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['code' => 404, 'message' => '商品不存在'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'message' => $validator->errors()->first()], 400);
        }

        $product->status = (int) $request->status;
        $product->save();

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $product,
        ]);
    }

    public function updateStock(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['code' => 404, 'message' => '商品不存在'], 404);
        }

        $validator = Validator::make($request->all(), [
            'stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'message' => $validator->errors()->first()], 400);
        }

        $product->stock = (int) $request->stock;
        $product->save();

        return response()->json([
            'code' => 200,
            'message' => '库存更新成功',
            'data' => $product,
        ]);
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['code' => 404, 'message' => '商品不存在'], 404);
        }

        $product->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
        ]);
    }
}
