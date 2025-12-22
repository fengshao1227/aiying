<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * 上传图片
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        try {
            $file = $request->file('file');

            // 生成唯一文件名
            $filename = date('Ymd') . '/' . Str::random(32) . '.' . $file->getClientOriginalExtension();

            // 存储文件到 public/uploads 目录
            $path = $file->storeAs('uploads/' . $filename, null, 'public');

            // 返回完整的可访问 URL
            $url = url('storage/' . $path);

            return response()->json([
                'code' => 200,
                'message' => '上传成功',
                'data' => [
                    'url' => $url,
                    'path' => $path,
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '上传失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 删除图片
     */
    public function deleteImage(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        try {
            $path = $request->input('path');

            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);

                return response()->json([
                    'code' => 200,
                    'message' => '删除成功',
                ]);
            }

            return response()->json([
                'code' => 404,
                'message' => '文件不存在',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '删除失败：' . $e->getMessage(),
            ], 500);
        }
    }
}
