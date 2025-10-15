<?php

namespace App\Enums;

enum ProductErrorCode: int
{

    case PRODUCT_NOT_FOUND = 2001;
    case PRODUCT_NOT_AVAILABLE = 2002;
    case INSUFFICIENT_STOCK = 2003;


    case MUST_PURCHASE_TO_REVIEW = 2101;
    case ALREADY_REVIEWED = 2102;

    public function message(): string
    {
        return match ($this) {
            self::PRODUCT_NOT_FOUND => 'Không tìm thấy sản phẩm.',
            self::PRODUCT_NOT_AVAILABLE => 'Sản phẩm này hiện không có sẵn.',
            self::INSUFFICIENT_STOCK => 'Sản phẩm không đủ số lượng trong kho.',
            self::MUST_PURCHASE_TO_REVIEW => 'Bạn phải mua sản phẩm này để có thể đánh giá.',
            self::ALREADY_REVIEWED => 'Bạn đã đánh giá sản phẩm này rồi.',
        };
    }
}
