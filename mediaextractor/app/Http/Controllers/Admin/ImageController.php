<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    protected $imageService;

    // Sử dụng constructor injection để service luôn sẵn sàng
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }
    public function index(Request $request)
    {
        // Lấy các tham số từ request (giữ nguyên)
        $currentPath = $request->query('path', '');
        $searchQuery = $request->query('search');
        $filter = $request->query('filter');

        // Lấy danh sách file (giữ nguyên)
        $files = $this->imageService->getFiles($currentPath, 30, $searchQuery, $filter);

        // ✅ THÊM MỚI: Xử lý request AJAX khi click vào thư mục
        // Nó khác với infinite scroll ở chỗ không có tham số 'page'
        if ($request->ajax() && !$request->has('page')) {
            return response()->json([
                'files'         => $files->items(),
                'breadcrumbs'   => collect(explode('/', $currentPath))->filter()->values(),
                'next_page_url' => $files->nextPageUrl(),
            ]);
        }

        // Xử lý request AJAX cho infinite scroll (giữ nguyên)
        if ($request->ajax() && $request->has('page')) {
            return response()->json([
                'files'         => $files->items(),
                'next_page_url' => $files->nextPageUrl(),
            ]);
        }

        // Chuẩn bị dữ liệu cho lần tải trang đầu tiên (giữ nguyên)
        $directoryTree = [['name' => 'Media', 'path' => '', 'children' => $this->imageService->getDirectoryTree()]];
        $breadcrumbs = collect(explode('/', $currentPath))->filter();
        $isPicker = $request->query('picker') === 'true';

        $viewData = [
            'directoryTree' => $directoryTree,
            'files'         => $files,
            'currentPath'   => $currentPath,
            'breadcrumbs'   => $breadcrumbs,
            'searchQuery'   => $searchQuery,
            'filter'        => $filter,
            'isPicker'      => $isPicker
        ];

        // Quyết định trả về view nào (giữ nguyên)
        if ($isPicker) {
            return view('images.picker', $viewData);
        }

        return view('images.index', $viewData);
    }
    /**
     * Xử lý upload nhiều file.
     */
    public function uploadFiles(Request $request)
    {
        $request->validate([
            'path' => 'nullable|string',
            'files' => 'required|array',
            'files.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120', // Giới hạn 5MB
            'convert_to_webp' => 'nullable|boolean',
        ]);

        $path = $request->input('path', '');
        $convertToWebp = $request->boolean('convert_to_webp');

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                try {
                    // Lấy tên gốc của file để làm baseName
                    $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $this->imageService->store($file, $path, $baseName, $convertToWebp);
                } catch (\Exception $e) {
                    Log::error('File upload failed: ' . $e->getMessage());
                    // Có thể trả về lỗi cho một file cụ thể nếu muốn
                }
            }
        }

        return back()->with('success', 'Tải file lên thành công!');
    }

    /**
     * Xóa nhiều file được chọn.
     */
    public function deleteFiles(Request $request)
    {
        $request->validate([
            'paths' => 'required|array',
            'paths.*' => 'string',
        ]);

        $paths = $request->input('paths');
        $deletedCount = 0;

        foreach ($paths as $path) {
            if ($this->imageService->delete($path)) {
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            return response()->json(['success' => true, 'message' => "Đã xóa thành công {$deletedCount} file."]);
        }

        return response()->json(['success' => false, 'message' => 'Không có file nào được xóa.'], 400);
    }


    /**
     * Tạo một thư mục mới.
     */
    public function createFolder(Request $request)
    {
        $request->validate([
            'parent_path' => 'nullable|string',
            'folder_name' => 'required|string|regex:/^[a-zA-Z0-9\-\_]+$/',
        ]);

        $parentPath = $request->input('parent_path', '');
        $folderName = $request->input('folder_name');
        $newPath = $parentPath ? "{$parentPath}/{$folderName}" : $folderName;

        if (Storage::disk('public')->exists($newPath)) {
            return back()->with('error', 'Thư mục đã tồn tại!');
        }

        Storage::disk('public')->makeDirectory($newPath);

        return back()->with('success', 'Tạo thư mục thành công!');
    }

    /**
     * Xóa một thư mục.
     */
    public function deleteFolder(Request $request)
    {
        $request->validate(['path' => 'required|string']);
        $path = $request->input('path');

        if (!Storage::disk('public')->exists($path)) {
            return back()->with('error', 'Thư mục không tồn tại!');
        }

        // Kiểm tra xem thư mục có trống không
        if (!empty(Storage::disk('public')->allFiles($path)) || !empty(Storage::disk('public')->allDirectories($path))) {
            return back()->with('error', 'Không thể xóa thư mục không trống!');
        }

        Storage::disk('public')->deleteDirectory($path);

        return redirect()->route('admin.images.index')->with('success', 'Xóa thư mục thành công!');
    }

    public function uploadFromEditor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'baseName' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first('image')], 422);
        }

        try {
            $file = $request->file('image');


            $baseName = $request->input('baseName', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));


            if (empty(trim($baseName))) {
                $baseName = 'post-content-image';
            }


            $path = $this->imageService->store(
                $file,
                'content',
                $baseName,
                true
            );

            $url = Storage::url($path);
            return response()->json(['location' => $url]);
        } catch (\Exception $e) {
            Log::error('TinyMCE Image Upload Failed: ' . $e->getMessage());
            return response()->json(['message' => 'Tải ảnh lên thất bại do lỗi hệ thống.'], 500);
        }
    }
}
