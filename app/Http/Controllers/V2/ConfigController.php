<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\SystemConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $group = $request->input('group');

        $query = SystemConfig::query();

        if ($group) {
            $query->byGroup($group);
        }

        $configs = $query->get()->mapWithKeys(function ($config) {
            return [$config->config_key => $config->value];
        });

        return $this->success($configs);
    }

    public function show(string $key): JsonResponse
    {
        $config = SystemConfig::byKey($key)->first();

        if (!$config) {
            return $this->error('配置不存在', 404);
        }

        return $this->success([
            'key' => $config->config_key,
            'value' => $config->value,
            'type' => $config->config_type,
            'group' => $config->group,
            'description' => $config->description,
        ]);
    }

    public function getPointsConfig(): JsonResponse
    {
        return $this->success([
            'exchange_rate' => SystemConfig::getPointsExchangeRate(),
            'max_discount_rate' => SystemConfig::getPointsMaxDiscountRate(),
        ]);
    }

    protected function success($data = null, string $message = '获取成功'): JsonResponse
    {
        return response()->json([
            'code' => 0,
            'message' => $message,
            'data' => $data,
        ]);
    }

    protected function error(string $message, int $httpCode = 400): JsonResponse
    {
        return response()->json([
            'code' => $httpCode,
            'message' => $message,
            'data' => null,
        ], $httpCode);
    }
}
