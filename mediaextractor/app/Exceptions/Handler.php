<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Exceptions\BaseException;
use App\Http\Traits\ApiResponse; // <-- Thêm trait ApiResponse

class Handler extends ExceptionHandler
{
    use ApiResponse; // <-- Sử dụng trait

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // SỬA: Xử lý tất cả các exception kế thừa từ BaseException
        // bằng cách sử dụng ApiResponse trait để trả về response nhất quán.
        $this->renderable(function (BaseException $e, $request) {
            if ($request->is('api/*')) {
                return $this->error(
                    $e->getMessage(),
                    $e->getStatusCode(),
                    $e->getErrorData()
                );
            }
        });

        // SỬA: Xử lý lỗi chưa xác thực (chưa đăng nhập) cho nhất quán
        $this->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return $this->error('Chưa xác thực.', 401);
            }
        });
    }
}
