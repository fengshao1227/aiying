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
            $extension = $file->getClientOriginalExtension();
            $filename = date('Ymd') . '_' . Str::random(32) . '.' . $extension;

            // 构建完整路径
            $directory = 'uploads';
            $relativePath = $directory . '/' . $filename;
            $fullPath = storage_path('app/public/' . $directory);

            // 确保目录存在且可写
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0775, true);
            }

            // 设置目录权限
            chmod($fullPath, 0775);

            // 检查目录是否可写
            if (!is_writable($fullPath)) {
                throw new \Exception("目录不可写: {$fullPath}");
            }

            // 移动文件
            $moved = $file->move($fullPath, $filename);

            if (!$moved) {
                throw new \Exception("文件移动失败");
            }

            // 返回完整的可访问 URL
            $url = url('storage/' . $relativePath);

            return response()->json([
                'code' => 200,
                'message' => '上传成功',
                'data' => [
                    'url' => $url,
                    'path' => $relativePath,
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('图片上传失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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
