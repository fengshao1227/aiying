<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->attributes->get('v2_user');

        $addresses = Address::forUser($user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->get();

        return $this->success($addresses);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->attributes->get('v2_user');

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'phone' => 'required|string|regex:/^1[3-9]\d{9}$/',
            'province' => 'required|string|max:50',
            'city' => 'required|string|max:50',
            'district' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        $validated['user_id'] = $user->id;
        $isDefault = $validated['is_default'] ?? false;
        unset($validated['is_default']);

        $address = DB::transaction(function () use ($validated, $isDefault, $user) {
            $address = Address::create($validated);

            if ($isDefault || Address::forUser($user->id)->count() === 1) {
                $address->setAsDefault();
            }

            return $address;
        });

        return $this->success($address, '添加成功');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('v2_user');

        $address = Address::forUser($user->id)->find($id);

        if (!$address) {
            return $this->error('地址不存在', 404);
        }

        return $this->success($address);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('v2_user');

        $address = Address::forUser($user->id)->find($id);

        if (!$address) {
            return $this->error('地址不存在', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:50',
            'phone' => 'sometimes|string|regex:/^1[3-9]\d{9}$/',
            'province' => 'sometimes|string|max:50',
            'city' => 'sometimes|string|max:50',
            'district' => 'sometimes|string|max:50',
            'address' => 'sometimes|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        $isDefault = $validated['is_default'] ?? null;
        unset($validated['is_default']);

        DB::transaction(function () use ($address, $validated, $isDefault) {
            $address->update($validated);

            if ($isDefault === true) {
                $address->setAsDefault();
            }
        });

        return $this->success($address->fresh(), '更新成功');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('v2_user');

        $address = Address::forUser($user->id)->find($id);

        if (!$address) {
            return $this->error('地址不存在', 404);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $newDefault = Address::forUser($user->id)->orderByDesc('updated_at')->first();
            if ($newDefault) {
                $newDefault->setAsDefault();
            }
        }

        return $this->success(null, '删除成功');
    }

    public function setDefault(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('v2_user');

        $address = Address::forUser($user->id)->find($id);

        if (!$address) {
            return $this->error('地址不存在', 404);
        }

        $address->setAsDefault();

        return $this->success($address, '设置成功');
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
