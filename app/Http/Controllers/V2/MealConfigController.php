<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\MealConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MealConfigController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'meal_type' => 'sometimes|in:breakfast,lunch,dinner',
            'date' => 'sometimes|date_format:Y-m-d',
        ]);

        $query = MealConfig::enabled();

        if (isset($validated['meal_type'])) {
            $query->byType($validated['meal_type']);
        }

        $configs = $query->get();

        if (isset($validated['date'])) {
            $date = Carbon::createFromFormat('Y-m-d', $validated['date'])->startOfDay();
            $configs->each(function ($config) use ($date) {
                $config->is_available = $config->isAvailableForDate($date);
            });
        }

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => $configs,
        ]);
    }
}
