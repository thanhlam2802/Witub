<?php

namespace App\Services;



use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;



use Illuminate\Pagination\LengthAwarePaginator;

class ImageService
{
    public function store(UploadedFile $file, string $folder, string $baseName, bool $convertToWebp = false, int $quality = 90): string
    {
        $fileName = Str::slug($baseName) . '-' . time();
        $disk = Storage::disk('public');

        if ($convertToWebp) {
            $filePath = "{$folder}/{$fileName}.webp";

            // 1. Đọc và chuyển đổi ảnh
            $image = Image::read($file);

            // 2. Sử dụng stream để lưu đối tượng Intervention Image đã được encode
            $disk->put($filePath, $image->toWebp($quality));

            // QUAN TRỌNG: $disk->put() với chuỗi nội dung (như toWebp() trả về) là phương pháp tốt nhất.
            // Phương pháp này đã được bạn áp dụng và nên hoạt động.
            // Nếu vẫn thấy file gốc, file gốc có thể đang được lưu bởi một tiến trình khác.

        } else {
            // Trường hợp 2: Giữ nguyên định dạng
            $extension = $file->getClientOriginalExtension();
            $filePath = "{$folder}/{$fileName}.{$extension}";

            // LƯU CÁCH CHUẨN CỦA LARAVEL: Sử dụng hàm storeAs()
            // Hàm này tự động dọn dẹp file tạm PHP sau khi lưu thành công.
            $file->storeAs($folder, "{$fileName}.{$extension}", 'public');

            // Bạn có thể bỏ qua dòng $disk->put(...) và quay lại dùng storeAs cho trường hợp này:
            // return $file->storeAs($folder, "{$fileName}.{$extension}", 'public');
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
