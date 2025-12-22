<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * 获取分类列表
     */
    public function index()
    {
        $categories = ProductCategory::orderBy('sort_order')->get();

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $categories,
        ]);
    }
}
