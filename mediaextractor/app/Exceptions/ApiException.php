<?php

namespace App\Exceptions;

use Illuminate\Http\Response;
use Throwable;

/**
 * Ném ra khi có lỗi xảy ra ở tầng hệ thống hoặc khi giao tiếp với các dịch vụ bên ngoài (API).
 * Đây là lỗi không lường trước được, không phải do người dùng.
 * Ví dụ: "Không thể kết nối đến máy chủ thanh toán", "Lỗi ghi file log", "Database không phản hồi".
 *
 * Class ApiException
 * @package App\Exceptions
 */
class ApiException extends BaseException
{
    public function __construct(
        string $message = "Hệ thống đang gặp sự cố, vui lòng thử lại sau.",
        $errorData = null,
        ?Throwable $previous = null
    ) {
        // Lỗi hệ thống thường là lỗi từ phía server (Internal Server Error).
        parent::__construct($message, Response::HTTP_INTERNAL_SERVER_ERROR, $errorData, $previous);
    }
}
