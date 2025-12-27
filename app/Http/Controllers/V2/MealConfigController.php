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
            'start_date' => 'sometimes|date_format:Y-m-d',
            'end_date' => 'sometimes|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $query = MealConfig::enabled();

        if (isset($validated['meal_type'])) {
            $query->byType($validated['meal_type']);
        }

        $meals = $query->get();

        $responseData = ['meals' => $meals];

        if (isset($validated['start_date']) && isset($validated['end_date'])) {
            $startDate = Carbon::createFromFormat('Y-m-d', $validated['start_date'])->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $validated['end_date'])->startOfDay();

            $unavailableDates = [];
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                $dateAvailable = false;
                foreach ($meals as $meal) {
                    if ($meal->isAvailableForDate($currentDate)) {
                        $dateAvailable = true;
                        break;
                    }
                }
                if (!$dateAvailable) {
                    $unavailableDates[] = $currentDate->format('Y-m-d');
                }
                $currentDate->addDay();
            }

            $responseData['unavailable_dates'] = $unavailableDates;
        }

        return response()->json([
            'code' => 0,
            'message' => '获取成功',
            'data' => $responseData,
        ]);
    }
}
