<?php

namespace App\Http\Controllers\Admin\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\MealConfig;
use Illuminate\Http\Request;

class MealConfigController extends Controller
{
    public function index()
    {
        $configs = MealConfig::orderBy('id')->get();

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => $configs,
        ]);
    }

    public function update(Request $request, $id)
    {
        $config = MealConfig::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:50',
            'price' => 'sometimes|numeric|min:0',
            'order_start_time' => 'sometimes|date_format:H:i:s',
            'order_end_time' => 'sometimes|date_format:H:i:s',
            'advance_days' => 'sometimes|integer|min:0',
            'description' => 'nullable|string|max:255',
            'status' => 'sometimes|in:0,1',
        ]);

        $config->update($validated);

        return response()->json([
            'code' => 0,
            'message' => '更新成功',
            'data' => $config->fresh(),
        ]);
    }
}
