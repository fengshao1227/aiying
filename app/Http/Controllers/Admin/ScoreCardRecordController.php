<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScoreCardRecord;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ScoreCardRecordController extends Controller
{
    /**
     * 获取客户的评分卡记录列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = ScoreCardRecord::query()->with('customer');

        // 客户ID筛选
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // 日期范围筛选
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('record_date', [$request->start_date, $request->end_date]);
        }

        // 排序
        $sortBy = $request->get('sort_by', 'record_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // 分页
        $perPage = $request->get('per_page', 15);
        $records = $query->paginate($perPage);

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $records
        ]);
    }

    /**
     * 获取评分卡详情
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $record = ScoreCardRecord::with('customer')->find($id);

        if (!$record) {
            return response()->json([
                'code' => 404,
                'message' => '评分卡记录不存在',
                'data' => null
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $record
        ]);
    }

    /**
     * 创建评分卡记录
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,customer_id',
            'card_number' => 'required|integer|min:1',
            'record_date' => 'required|date',
            'score_data' => 'nullable|array',
        ], [
            'customer_id.required' => '请选择客户',
            'customer_id.exists' => '客户不存在',
            'card_number.required' => '请输入评分卡编号',
            'card_number.min' => '评分卡编号至少为1',
            'record_date.required' => '请选择记录日期',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
                'data' => null
            ], 400);
        }

        $record = ScoreCardRecord::create($request->all());

        return response()->json([
            'code' => 201,
            'message' => '创建成功',
            'data' => $record
        ], 201);
    }

    /**
     * 更新评分卡记录
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $record = ScoreCardRecord::find($id);

        if (!$record) {
            return response()->json([
                'code' => 404,
                'message' => '评分卡记录不存在',
                'data' => null
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'card_number' => 'sometimes|required|integer|min:1',
            'record_date' => 'sometimes|required|date',
            'score_data' => 'nullable|array',
        ], [
            'card_number.required' => '请输入评分卡编号',
            'card_number.min' => '评分卡编号至少为1',
            'record_date.required' => '请选择记录日期',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
                'data' => null
            ], 400);
        }

        $record->update($request->all());

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $record
        ]);
    }

    /**
     * 删除评分卡记录
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $record = ScoreCardRecord::find($id);

        if (!$record) {
            return response()->json([
                'code' => 404,
                'message' => '评分卡记录不存在',
                'data' => null
            ], 404);
        }

        $record->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
            'data' => null
        ]);
    }

    /**
     * 获取客户评分卡时间线
     */
    public function timeline($customerId)
    {
        $customer = Customer::find($customerId);

        if (!$customer) {
            return response()->json([
                'code' => 404,
                'message' => '客户不存在',
                'data' => null
            ], 404);
        }

        if (!$customer->check_in_date) {
            return response()->json([
                'code' => 400,
                'message' => '客户尚未入住',
                'data' => null
            ], 400);
        }

        $checkIn = Carbon::parse($customer->check_in_date);
        $checkOut = $customer->check_out_date ? Carbon::parse($customer->check_out_date) : null;
        $today = Carbon::today();

        $cardSlots = $this->generateCardSlots($checkIn, $checkOut);

        $existingRecords = ScoreCardRecord::where('customer_id', $customerId)
            ->get()
            ->keyBy('card_number');

        $timeline = [];
        foreach ($cardSlots as $slot) {
            $record = $existingRecords->get($slot['card_number']);
            $targetDate = Carbon::parse($slot['target_date']);
            $isOverdue = $targetDate->lt($today) && (!$record || !$record->image_url);

            $timeline[] = [
                'card_number' => $slot['card_number'],
                'card_type' => $slot['card_type'],
                'target_date' => $slot['target_date'],
                'record_id' => $record ? $record->record_id : null,
                'image_url' => $record ? $record->image_url : null,
                'status' => $record && $record->image_url ? 'uploaded' : ($isOverdue ? 'overdue' : 'pending'),
                'created_at' => $record ? $record->created_at : null,
            ];
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => [
                'customer' => [
                    'customer_id' => $customer->customer_id,
                    'customer_name' => $customer->customer_name,
                    'check_in_date' => $customer->check_in_date ? $customer->check_in_date->format('Y-m-d') : null,
                    'check_out_date' => $customer->check_out_date ? $customer->check_out_date->format('Y-m-d') : null,
                ],
                'timeline' => $timeline,
            ]
        ]);
    }

    /**
     * 上传评分卡图片
     */
    public function uploadCard(Request $request, $customerId)
    {
        $validator = Validator::make($request->all(), [
            'card_number' => 'required|integer|min:1',
            'image_url' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
                'data' => null
            ], 400);
        }

        $customer = Customer::find($customerId);
        if (!$customer) {
            return response()->json([
                'code' => 404,
                'message' => '客户不存在',
                'data' => null
            ], 404);
        }

        if (!$customer->check_in_date) {
            return response()->json([
                'code' => 400,
                'message' => '客户尚未入住',
                'data' => null
            ], 400);
        }

        $cardNumber = $request->card_number;
        $checkIn = Carbon::parse($customer->check_in_date);
        $checkOut = $customer->check_out_date ? Carbon::parse($customer->check_out_date) : null;
        $cardSlots = $this->generateCardSlots($checkIn, $checkOut);

        $slot = collect($cardSlots)->firstWhere('card_number', $cardNumber);
        if (!$slot) {
            return response()->json([
                'code' => 400,
                'message' => '无效的评分卡编号',
                'data' => null
            ], 400);
        }

        $record = ScoreCardRecord::updateOrCreate(
            ['customer_id' => $customerId, 'card_number' => $cardNumber],
            [
                'record_date' => Carbon::today()->format('Y-m-d'),
                'image_url' => $request->image_url,
                'status' => 1,
                'target_date' => $slot['target_date'],
                'card_type' => $slot['card_type'],
            ]
        );

        return response()->json([
            'code' => 200,
            'message' => '上传成功',
            'data' => $record
        ]);
    }

    /**
     * 生成评分卡卡槽
     */
    private function generateCardSlots(Carbon $checkIn, ?Carbon $checkOut): array
    {
        $slots = [];
        $cardNumber = 1;

        // 第一张：入住后2天内
        $slots[] = [
            'card_number' => $cardNumber++,
            'card_type' => 'initial',
            'target_date' => $checkIn->copy()->addDays(2)->format('Y-m-d'),
        ];

        // 周卡：每满一个礼拜
        $weeklyCards = [];
        $weekNumber = 1;
        while (true) {
            $weekDate = $checkIn->copy()->addWeeks($weekNumber);
            if ($checkOut && $weekDate->gt($checkOut)) {
                break;
            }
            if (!$checkOut && $weekNumber > 12) {
                break;
            }
            $weeklyCards[] = [
                'card_number' => $cardNumber++,
                'card_type' => 'weekly',
                'target_date' => $weekDate->format('Y-m-d'),
            ];
            $weekNumber++;
        }

        // 末次卡：离所前一天
        $finalCard = null;
        if ($checkOut) {
            $finalDate = $checkOut->copy()->subDay();
            $finalCard = [
                'card_number' => $cardNumber,
                'card_type' => 'final',
                'target_date' => $finalDate->format('Y-m-d'),
            ];

            // 冲突处理：如果末次卡与最后一张周卡相差3天内，取消该周卡
            if (!empty($weeklyCards)) {
                $lastWeeklyDate = Carbon::parse(end($weeklyCards)['target_date']);
                if (abs($finalDate->diffInDays($lastWeeklyDate)) <= 3) {
                    array_pop($weeklyCards);
                    $finalCard['card_number'] = $cardNumber - 1;
                }
            }
        }

        $slots = array_merge($slots, $weeklyCards);
        if ($finalCard) {
            $slots[] = $finalCard;
        }

        // 重新编号
        foreach ($slots as $i => &$slot) {
            $slot['card_number'] = $i + 1;
        }

        return $slots;
    }
}
