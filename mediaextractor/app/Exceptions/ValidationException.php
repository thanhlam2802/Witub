<?php

namespace App\Exceptions;

use Illuminate\Http\Response;
use Throwable;

/**
 * Ném ra khi dữ liệu đầu vào từ người dùng không vượt qua được các quy tắc xác thực.
 * Thường được sử dụng để trả về một danh sách các lỗi chi tiết cho từng trường dữ liệu.
 *
 * Class ValidationException
 * @package App\Exceptions
 */
class ValidationException extends BaseException
{
    public function __construct(
        string $message = "Dữ liệu cung cấp không hợp lệ.",
        $errorData = null,
        ?Throwable $previous = null
    ) {

        parent::__construct($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errorData, $previous);
    }
}
