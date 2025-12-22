<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScoreCardRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
}
