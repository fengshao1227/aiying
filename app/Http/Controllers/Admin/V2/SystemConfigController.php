<?php

namespace App\Http\Controllers\Admin\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\SystemConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SystemConfigController extends Controller
{
    public function index(Request $request)
    {
        $query = SystemConfig::query();

        if ($request->filled('group')) {
            $query->byGroup($request->group);
        }

        if ($request->filled('keyword')) {
            $keyword = addcslashes($request->keyword, '%_');
            $query->where(function ($q) use ($keyword) {
                $q->where('config_key', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        $configs = $query->orderBy('group')->orderBy('config_key')->get();

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $configs,
        ]);
    }

    public function show($id)
    {
        $config = SystemConfig::find($id);

        if (!$config) {
            return response()->json(['code' => 404, 'message' => '配置不存在'], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $config,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'config_key' => 'required|string|max:100|unique:system_configs,config_key',
            'config_value' => 'required|string',
            'config_type' => 'required|in:string,number,boolean,json',
            'group' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'message' => $validator->errors()->first()], 400);
        }

        $config = SystemConfig::create($request->only([
            'config_key', 'config_value', 'config_type', 'group', 'description'
        ]));

        return response()->json([
            'code' => 200,
            'message' => '创建成功',
            'data' => $config,
        ]);
    }

    public function update(Request $request, $id)
    {
        $config = SystemConfig::find($id);

        if (!$config) {
            return response()->json(['code' => 404, 'message' => '配置不存在'], 404);
        }

        $validator = Validator::make($request->all(), [
            'config_value' => 'sometimes|required|string',
            'config_type' => 'sometimes|required|in:string,number,boolean,json',
            'group' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'message' => $validator->errors()->first()], 400);
        }

        $config->update($request->only([
            'config_value', 'config_type', 'group', 'description'
        ]));

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $config,
        ]);
    }

    public function destroy($id)
    {
        $config = SystemConfig::find($id);

        if (!$config) {
            return response()->json(['code' => 404, 'message' => '配置不存在'], 404);
        }

        $config->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
        ]);
    }

    public function batchUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'configs' => 'required|array',
            'configs.*.config_key' => 'required|string',
            'configs.*.config_value' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'message' => $validator->errors()->first()], 400);
        }

        DB::transaction(function () use ($request) {
            foreach ($request->configs as $item) {
                SystemConfig::where('config_key', $item['config_key'])
                    ->update(['config_value' => $item['config_value']]);
            }
        });

        return response()->json([
            'code' => 200,
            'message' => '批量更新成功',
        ]);
    }

    public function groups()
    {
        $groups = SystemConfig::select('group')
            ->distinct()
            ->whereNotNull('group')
            ->pluck('group');

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $groups,
        ]);
    }
}
