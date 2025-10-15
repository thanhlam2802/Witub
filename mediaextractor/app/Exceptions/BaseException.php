<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Throwable;

abstract class BaseException extends Exception
{
    protected int $statusCode;
    protected $errorData;

    public function __construct(
        string $message = "Đã có lỗi xảy ra.",
        int $statusCode = 500,
        $errorData = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->errorData = $errorData;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorData()
    {
        return $this->errorData;
    }

    /**
     * Hàm này giúp Laravel tự động trả JSON thay vì lỗi 500
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'code'    => $this->statusCode,
            'status'  => 'error',
            'message' => $this->getMessage(),
            'errors'  => $this->errorData,
            'data'    => null,
        ], $this->statusCode);
    }
}
