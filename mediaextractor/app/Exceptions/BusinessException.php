<?php

namespace App\Exceptions;

use Illuminate\Http\Response;
use Throwable;

/**
 * Ném ra khi một hành động vi phạm quy tắc nghiệp vụ của ứng dụng.
 * Đây là lỗi do người dùng hoặc do một trạng thái dữ liệu không cho phép.
 * Ví dụ: "Sản phẩm đã hết hàng", "Người dùng không đủ tuổi", "Mật khẩu không đủ mạnh".
 *
 * Class BusinessException
 * @package App\Exceptions
 */
class BusinessException extends BaseException
{
    public function __construct(
        string $message = "Thao tác không hợp lệ.",
        $errorData = null,
        ?Throwable $previous = null
    ) {
        // Lỗi nghiệp vụ thường là lỗi từ phía client (Bad Request).
        parent::__construct($message, Response::HTTP_BAD_REQUEST, $errorData, $previous);
    }
}
