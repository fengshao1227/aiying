<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * 获取客户列表（带分页和搜索）
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        // 关键字搜索（客户姓名、电话）
        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('customer_name', 'like', "%{$keyword}%")
                  ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        // 套餐筛选
        if ($request->has('package_name')) {
            $query->where('package_name', $request->package_name);
        }

        // 入住状态筛选（是否已入住）
        if ($request->has('is_checked_in')) {
            if ($request->is_checked_in == 1) {
                $query->whereNotNull('check_in_date')
                      ->whereNull('check_out_date');
            } else {
                $query->where(function ($q) {
                    $q->whereNull('check_in_date')
                      ->orWhereNotNull('check_out_date');
                });
            }
        }

        // 排序
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // 分页
        $perPage = $request->get('per_page', 15);
        $customers = $query->paginate($perPage);

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $customers
        ]);
    }

    /**
     * 获取客户详情
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $customer = Customer::with(['roomStatuses.room', 'scoreCardRecords'])
            ->find($id);

        if (!$customer) {
            return response()->json([
                'code' => 404,
                'message' => '客户不存在',
                'data' => null
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $customer
        ]);
    }

    /**
     * 创建客户
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:50',
            'phone' => 'required|string|max:20',
            'package_name' => 'nullable|string|max:100',
            'baby_name' => 'nullable|string|max:50',
            'mother_birthday' => 'nullable|date',
            'baby_birthday' => 'nullable|date',
            'nanny_name' => 'nullable|string|max:50',
            'due_date' => 'nullable|date',
            'address' => 'nullable|string',
            'check_in_date' => 'nullable|date',
            'check_out_date' => 'nullable|date|after:check_in_date',
            'remarks' => 'nullable|string',
        ], [
            'customer_name.required' => '请输入客户姓名',
            'phone.required' => '请输入联系电话',
            'check_out_date.after' => '出所时间必须晚于入住时间',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
                'data' => null
            ], 400);
        }

        $customer = Customer::create($request->all());

        return response()->json([
            'code' => 201,
            'message' => '创建成功',
            'data' => $customer
        ], 201);
    }

    /**
     * 更新客户
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'code' => 404,
                'message' => '客户不存在',
                'data' => null
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'customer_name' => 'sometimes|required|string|max:50',
            'phone' => 'sometimes|required|string|max:20',
            'package_name' => 'nullable|string|max:100',
            'baby_name' => 'nullable|string|max:50',
            'mother_birthday' => 'nullable|date',
            'baby_birthday' => 'nullable|date',
            'nanny_name' => 'nullable|string|max:50',
            'due_date' => 'nullable|date',
            'address' => 'nullable|string',
            'check_in_date' => 'nullable|date',
            'check_out_date' => 'nullable|date|after:check_in_date',
            'remarks' => 'nullable|string',
        ], [
            'customer_name.required' => '请输入客户姓名',
            'phone.required' => '请输入联系电话',
            'check_out_date.after' => '出所时间必须晚于入住时间',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
                'data' => null
            ], 400);
        }

        $customer->update($request->all());

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $customer
        ]);
    }

    /**
     * 删除客户
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'code' => 404,
                'message' => '客户不存在',
                'data' => null
            ], 404);
        }

        // 删除客户（级联删除相关的房态记录和评分卡记录）
        $customer->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
            'data' => null
        ]);
    }
}
