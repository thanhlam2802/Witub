<?php

namespace App\Services;



use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;



use Illuminate\Pagination\LengthAwarePaginator;

class ImageService
{
    /**
     * Lưu một file ảnh được tải lên, tùy chọn chuyển đổi sang WebP.
     *
     * @param UploadedFile $file Đối tượng file được tải lên từ request.
     * @param string $folder Thư mục con để lưu ảnh.
     * @param string $baseName Tên gốc để tạo slug cho file (VD: tên sản phẩm, tên danh mục).
     * @param bool $convertToWebp True nếu muốn chuyển đổi ảnh sang định dạng WebP.
     * @param int $quality Chất lượng ảnh WebP (từ 0 đến 100).
     * @return string Đường dẫn tương đối của file đã lưu.
     */
    // THÊM THAM SỐ $baseName VÀO ĐÂY
    public function store(UploadedFile $file, string $folder, string $baseName, bool $convertToWebp = false, int $quality = 90): string
    {

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileName = Str::slug($baseName) . '-' . time();


        $filePath = "{$folder}/{$fileName}";

        if ($convertToWebp) {
            $filePath .= '.webp';


            $image = Image::read($file)->toWebp($quality);

            Storage::disk('public')->put($filePath, (string) $image);
        } else {

            $filePath .= '.' . $file->getClientOriginalExtension();

            $file->storeAs($folder, $fileName . '.' . $file->getClientOriginalExtension(), 'public');
        }

        return $filePath;
    }

    /**
     * Xóa một file ảnh khỏi storage.
     *
     * @param string|null $path Đường dẫn tương đối của file trong disk 'public'.
     * @return bool True nếu xóa thành công hoặc đường dẫn rỗng.
     */
    public function delete(?string $path): bool
    {
        if (!$path) {
            return true;
        }

        return Storage::disk('public')->delete($path);
    }

    /**
     * Lấy cấu trúc cây thư mục từ một đường dẫn gốc.
     *
     * @param string $path Đường dẫn gốc để quét.
     * @return array Cấu trúc cây thư mục.
     */
    public function getDirectoryTree(string $path = ''): array
    {
        $directories = Storage::disk('public')->directories($path);

        $tree = [];
        foreach ($directories as $directory) {
            $tree[] = [
                'name' => basename($directory),
                'path' => $directory,

                'children' => $this->getDirectoryTree($directory)
            ];
        }

        return $tree;
    }

    /**
     * Lấy danh sách các file trong một thư mục cụ thể và phân trang.
     *
     * @param string $folder Đường dẫn thư mục cần lấy file.
     * @param int $perPage Số lượng file trên mỗi trang.
     * @param string|null $searchQuery Chuỗi tìm kiếm tên file.
     * @param string|null $filter Bộ lọc áp dụng (ví dụ: 'today').
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getFiles(string $folder, int $perPage = 30, ?string $searchQuery = null, ?string $filter = null): LengthAwarePaginator
    {
        $allFiles = Storage::disk('public')->files($folder);

        $filesCollection = collect($allFiles);

        // THÊM MỚI: LOGIC LỌC THEO NGÀY
        if ($filter === 'today') {
            $todayStart = now()->startOfDay()->timestamp;
            $todayEnd = now()->endOfDay()->timestamp;

            $filesCollection = $filesCollection->filter(function ($file) use ($todayStart, $todayEnd) {
                $lastModified = Storage::disk('public')->lastModified($file);
                return $lastModified >= $todayStart && $lastModified <= $todayEnd;
            });
        }

        // Logic tìm kiếm (giữ nguyên)
        if ($searchQuery) {
            $filesCollection = $filesCollection->filter(
                fn($file) => Str::contains(basename($file), $searchQuery, true)
            );
        }

        $files = $filesCollection->map(function ($file) {
            return [
                'name' => basename($file),
                'path' => $file,
                'url' => Storage::disk('public')->url($file),
                'size' => $this->formatFileSize(Storage::disk('public')->size($file)),
                'last_modified' => Storage::disk('public')->lastModified($file),
            ];
        })
            // SẮP XẾP: Dòng này đảm bảo file mới nhất luôn ở trên cùng
            ->sortByDesc('last_modified')
            ->map(function ($file) {
                // Chuyển đổi timestamp sang định dạng ngày tháng sau khi đã sắp xếp
                $file['last_modified'] = date('d-m-Y H:i', $file['last_modified']);
                return $file;
            })
            ->values();

        // Phân trang thủ công (giữ nguyên)
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $files->slice(($currentPage - 1) * $perPage, $perPage)->all();

        return new LengthAwarePaginator($currentPageItems, $files->count(), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath()
        ]);
    }


    /**
     * Định dạng kích thước file.
     */
    private function formatFileSize($bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
}
