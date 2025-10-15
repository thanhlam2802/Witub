<?php

namespace App\Http\Traits;

trait ApiResponse
{
    /**
     * Response thành công
     */
    public static function success($data = null, string $message = 'Thành công', int $status = 200)
    {
        return response()->json([
            'code'    => $status,
            'status'  => 'success',
            'message' => $message,
            'errors'  => null,
            'data'    => $data,
        ], $status);
    }

    /**
     * Response thất bại
     */
    public static function error(string $message = 'Có lỗi xảy ra', int $status = 400, $errors = null, $data = null)
    {
        return response()->json([
            'code'    => $status,
            'status'  => 'error',
            'message' => $message,
            'errors'  => $errors,
            'data'    => $data,
        ], $status);
    }
}
