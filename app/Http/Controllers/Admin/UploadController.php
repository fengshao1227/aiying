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
            if (!$request->hasFile('file')) {
                throw new \Exception('未检测到上传文件');
            }

            $file = $request->file('file');

            if (!$file->isValid()) {
                throw new \Exception('上传文件无效');
            }

            // 生成文件名
            $filename = date('Ymd') . '_' . Str::random(16) . '.' . $file->getClientOriginalExtension();

            // 目标路径
            $uploadPath = storage_path('app/public/uploads');

            // 确保目录存在
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0775, true);
            }

            // 移动上传文件
            $file->move($uploadPath, $filename);

            // 相对路径
            $relativePath = 'uploads/' . $filename;

            // 生成访问URL
            $url = url('storage/' . $relativePath);

            return response()->json([
                'code' => 200,
                'message' => '上传成功',
                'data' => [
                    'url' => $url,
                    'path' => $relativePath,
                    'filename' => $file->getClientOriginalName(),
                    'size' => filesize($uploadPath . '/' . $filename),
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
