<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class BaseService
{
    public function __construct()
    {
        //
    }

    /**
     * Ghi log lỗi
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error($message, $context);
    }


    protected function validatePermission(string $action): void
    {
        $user = Auth::user();
        if (!$user || !$user->can($action)) {
            $this->logError("Người dùng không có quyền: {$action}", ['user_id' => $user?->id]);
            throw new Exception('Bạn không có quyền thực hiện hành động này.');
        }
    }

    /**
     * Trả về dữ liệu thành công chuẩn hóa
     */
    protected function successResponse(mixed $data = null, string $message = 'Thành công'): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
    }

    /**
     * Trả về lỗi chuẩn hóa
     */
    protected function errorResponse(string $message = 'Đã xảy ra lỗi', array $errors = []): array
    {
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ];
    }


    protected function executeSafely(callable $callback): array
    {
        try {
            $result = $callback();
            return $this->successResponse($result);
        } catch (Exception $e) {
            $this->logError($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->errorResponse($e->getMessage());
        }
    }
}
