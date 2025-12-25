<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomStatus;
use App\Models\Room;
use App\Models\V2\User;
use App\Models\V2\SystemConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RoomStatusController extends Controller
{
    /**
     * 获取指定月份的房态列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'record_month' => 'required|date_format:Y-m',
        ], [
            'record_month.required' => '请指定查询月份',
            'record_month.date_format' => '月份格式错误，应为YYYY-MM',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
                'data' => null
            ], 400);
        }

        $recordMonth = $request->record_month;

        // 获取所有房间及其房态
        $rooms = Room::with(['roomStatuses' => function ($query) use ($recordMonth) {
            $query->where('record_month', $recordMonth)
                  ->with('customer'); // 加载客户信息
        }])->orderBy('display_order')->get();

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $rooms
        ]);
    }

    /**
     * 创建房态记录
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,room_id',
            'customer_id' => 'nullable|exists:customers,customer_id',
            'check_in_date' => 'nullable|date',
            'check_out_date' => 'nullable|date|after:check_in_date',
            'status' => 'required|integer|in:0,1,2',
            'record_month' => 'required|date_format:Y-m',
        ], [
            'room_id.required' => '请选择房间',
            'room_id.exists' => '房间不存在',
            'customer_id.exists' => '客户不存在',
            'check_out_date.after' => '退房日期必须晚于入住日期',
            'status.required' => '请选择状态',
            'status.in' => '状态值无效',
            'record_month.required' => '请指定记录月份',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
                'data' => null
            ], 400);
        }

        // 检查是否已存在该月份的房态记录
        $exists = RoomStatus::where('room_id', $request->room_id)
            ->where('record_month', $request->record_month)
            ->exists();

        if ($exists) {
            return response()->json([
                'code' => 400,
                'message' => '该房间在此月份已有房态记录',
                'data' => null
            ], 400);
        }

        $roomStatus = RoomStatus::create($request->all());

        return response()->json([
            'code' => 201,
            'message' => '创建成功',
            'data' => $roomStatus
        ], 201);
    }

    /**
     * 更新房态记录
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $roomStatus = RoomStatus::find($id);

        if (!$roomStatus) {
            return response()->json([
                'code' => 404,
                'message' => '房态记录不存在',
                'data' => null
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,customer_id',
            'check_in_date' => 'nullable|date',
            'check_out_date' => 'nullable|date|after:check_in_date',
            'status' => 'sometimes|required|integer|in:0,1,2',
        ], [
            'customer_id.exists' => '客户不存在',
            'check_out_date.after' => '退房日期必须晚于入住日期',
            'status.in' => '状态值无效',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
                'data' => null
            ], 400);
        }

        $roomStatus->update($request->all());

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $roomStatus
        ]);
    }

    /**
     * 删除房态记录
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $roomStatus = RoomStatus::find($id);

        if (!$roomStatus) {
            return response()->json([
                'code' => 404,
                'message' => '房态记录不存在',
                'data' => null
            ], 404);
        }

        $roomStatus->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
            'data' => null
        ]);
    }

    /**
     * 办理入住（创建跨月房态记录）
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,room_id',
            'customer_id' => 'required|exists:customers,customer_id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
        ], [
            'room_id.required' => '请选择房间',
            'customer_id.required' => '请选择客户',
            'check_in_date.required' => '请选择入住日期',
            'check_out_date.required' => '请选择退房日期',
            'check_out_date.after' => '退房日期必须晚于入住日期',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
                'data' => null
            ], 400);
        }

        try {
            DB::beginTransaction();

            $checkInDate = Carbon::parse($request->check_in_date);
            $checkOutDate = Carbon::parse($request->check_out_date);

            // 生成跨月房态记录
            $months = [];
            $current = $checkInDate->copy()->startOfMonth();
            $end = $checkOutDate->copy()->startOfMonth();

            while ($current->lte($end)) {
                $months[] = $current->format('Y-m');
                $current->addMonth();
            }

            // 为每个月份创建房态记录
            foreach ($months as $month) {
                RoomStatus::create([
                    'room_id' => $request->room_id,
                    'customer_id' => $request->customer_id,
                    'check_in_date' => $request->check_in_date,
                    'check_out_date' => $request->check_out_date,
                    'status' => RoomStatus::STATUS_OCCUPIED,
                    'record_month' => $month,
                ]);
            }

            // 入住赠送积分（事务内）
            $this->grantCheckinPoints($request->customer_id);

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => '入住办理成功',
                'data' => [
                    'room_id' => $request->room_id,
                    'customer_id' => $request->customer_id,
                    'months' => $months,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'code' => 500,
                'message' => '入住办理失败：' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    protected function grantCheckinPoints(int $customerId): void
    {
        $points = SystemConfig::getCheckinPointsAmount();
        if ($points <= 0) {
            return;
        }

        $user = User::where('customer_id', $customerId)->first();
        if (!$user) {
            return;
        }

        try {
            $user->addPoints($points, 'earn', 'checkin', null, '入住赠送积分');
        } catch (\Exception $e) {
            Log::error('Grant checkin points failed', [
                'user_id' => $user->id,
                'customer_id' => $customerId,
                'points' => $points,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 空房查询 - 查询指定房型在指定月份的空余房间及前后月入住情况
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function vacantRooms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_type' => 'required|string',
            'month' => 'required|date_format:Y-m',
        ], [
            'room_type.required' => '请选择房型',
            'month.required' => '请选择月份',
            'month.date_format' => '月份格式错误，应为YYYY-MM',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
                'data' => null
            ], 400);
        }

        $roomType = $request->room_type;
        $month = $request->month;
        $currentMonth = Carbon::createFromFormat('Y-m', $month);
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        // 获取指定房型的所有房间
        $rooms = Room::where('room_type', $roomType)
            ->orderBy('display_order')
            ->get();

        // 查询这些房间在当月是否有入住记录（status=1）
        $occupiedRoomIds = RoomStatus::whereIn('room_id', $rooms->pluck('room_id'))
            ->where('record_month', $month)
            ->where('status', RoomStatus::STATUS_OCCUPIED)
            ->pluck('room_id')
            ->toArray();

        // 筛选出空余房间
        $vacantRooms = $rooms->filter(function ($room) use ($occupiedRoomIds) {
            return !in_array($room->room_id, $occupiedRoomIds);
        });

        // 获取空余房间的三个月入住情况
        $result = [];
        foreach ($vacantRooms as $room) {
            $occupancy = [
                'prev_month' => $this->getMonthOccupancy($room->room_id, $prevMonth),
                'current_month' => $this->getMonthOccupancy($room->room_id, $month),
                'next_month' => $this->getMonthOccupancy($room->room_id, $nextMonth),
            ];

            $result[] = [
                'room_id' => $room->room_id,
                'room_name' => $room->room_name,
                'floor' => $room->floor,
                'room_type' => $room->room_type,
                'color_code' => $room->color_code,
                'occupancy' => $occupancy,
            ];
        }

        // 获取所有可用房型列表
        $roomTypes = Room::whereNotNull('room_type')
            ->where('room_type', '!=', '')
            ->distinct()
            ->pluck('room_type')
            ->sort()
            ->values();

        return response()->json([
            'code' => 200,
            'message' => '查询成功',
            'data' => [
                'vacant_rooms' => $result,
                'room_types' => $roomTypes,
                'query_month' => $month,
                'prev_month' => $prevMonth,
                'next_month' => $nextMonth,
            ]
        ]);
    }

    /**
     * 获取指定房间在指定月份的入住记录
     */
    private function getMonthOccupancy(int $roomId, string $month): array
    {
        $records = RoomStatus::where('room_id', $roomId)
            ->where('record_month', $month)
            ->where('status', RoomStatus::STATUS_OCCUPIED)
            ->with('customer:customer_id,customer_name')
            ->get();

        return $records->map(function ($record) {
            return [
                'customer_name' => $record->customer?->customer_name ?? '未知客户',
                'check_in_date' => $record->check_in_date?->format('Y-m-d'),
                'check_out_date' => $record->check_out_date?->format('Y-m-d'),
            ];
        })->toArray();
    }

    /**
     * 办理退房
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,room_id',
            'customer_id' => 'required|exists:customers,customer_id',
        ], [
            'room_id.required' => '请选择房间',
            'customer_id.required' => '请选择客户',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
                'data' => null
            ], 400);
        }

        try {
            DB::beginTransaction();

            // 查找该客户在该房间的所有房态记录
            $statuses = RoomStatus::where('room_id', $request->room_id)
                ->where('customer_id', $request->customer_id)
                ->where('status', RoomStatus::STATUS_OCCUPIED)
                ->get();

            if ($statuses->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'message' => '未找到该客户的入住记录',
                    'data' => null
                ], 404);
            }

            // 将状态改为空闲，清空客户信息
            foreach ($statuses as $status) {
                $status->update([
                    'status' => RoomStatus::STATUS_VACANT,
                    'customer_id' => null,
                    'check_in_date' => null,
                    'check_out_date' => null,
                ]);
            }

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => '退房办理成功',
                'data' => [
                    'updated_count' => $statuses->count(),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'code' => 500,
                'message' => '退房办理失败：' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
