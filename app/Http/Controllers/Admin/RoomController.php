<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    /**
     * 获取房间列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Room::query();

        // 楼层筛选
        if ($request->has('floor')) {
            $query->where('floor', $request->floor);
        }

        // 房型筛选
        if ($request->has('room_type')) {
            $query->where('room_type', $request->room_type);
        }

        // 关键字搜索
        if ($request->has('keyword')) {
            $query->where('room_name', 'like', "%{$request->keyword}%");
        }

        // 排序
        $sortBy = $request->get('sort_by', 'display_order');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // 是否分页
        if ($request->get('paginate', true)) {
            $perPage = $request->get('per_page', 15);
            $rooms = $query->paginate($perPage);
        } else {
            $rooms = $query->get();
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $rooms
        ]);
    }

    /**
     * 获取房间详情
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $room = Room::with('roomStatuses')->find($id);

        if (!$room) {
            return response()->json([
                'code' => 404,
                'message' => '房间不存在',
                'data' => null
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $room
        ]);
    }

    /**
     * 创建房间
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_name' => 'required|string|max:50|unique:rooms,room_name',
            'floor' => 'required|integer|in:1,2',
            'room_type' => 'nullable|string|max:50',
            'color_code' => 'nullable|string|max:7',
            'ac_group_id' => 'nullable|integer',
            'display_order' => 'nullable|integer',
        ], [
            'room_name.required' => '请输入房间名称',
            'room_name.unique' => '房间名称已存在',
            'floor.required' => '请选择楼层',
            'floor.in' => '楼层只能是1或2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
                'data' => null
            ], 400);
        }

        $room = Room::create($request->all());

        return response()->json([
            'code' => 201,
            'message' => '创建成功',
            'data' => $room
        ], 201);
    }

    /**
     * 更新房间
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'code' => 404,
                'message' => '房间不存在',
                'data' => null
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'room_name' => 'sometimes|required|string|max:50|unique:rooms,room_name,' . $id . ',room_id',
            'floor' => 'sometimes|required|integer|in:1,2',
            'room_type' => 'nullable|string|max:50',
            'color_code' => 'nullable|string|max:7',
            'ac_group_id' => 'nullable|integer',
            'display_order' => 'nullable|integer',
        ], [
            'room_name.required' => '请输入房间名称',
            'room_name.unique' => '房间名称已存在',
            'floor.required' => '请选择楼层',
            'floor.in' => '楼层只能是1或2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => $validator->errors()->first(),
                'data' => null
            ], 400);
        }

        $room->update($request->all());

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $room
        ]);
    }

    /**
     * 删除房间
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'code' => 404,
                'message' => '房间不存在',
                'data' => null
            ], 404);
        }

        // 检查是否有关联的房态记录
        if ($room->roomStatuses()->exists()) {
            return response()->json([
                'code' => 400,
                'message' => '该房间存在房态记录，无法删除',
                'data' => null
            ], 400);
        }

        $room->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
            'data' => null
        ]);
    }
}
