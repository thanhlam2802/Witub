<?php

namespace App\Enums;

enum CartErrorCode: int
{
    case CART_EMPTY = 3001;
    case ITEM_NOT_FOUND_IN_CART = 3002;
    case QUANTITY_EXCEEDS_STOCK = 3003;
    case MAX_ITEM_QUANTITY_REACHED = 3004;

    public function message(): string
    {
        return match ($this) {
            self::CART_EMPTY => 'Giỏ hàng của bạn đang trống.',
            self::ITEM_NOT_FOUND_IN_CART => 'Không tìm thấy sản phẩm này trong giỏ hàng.',
            self::QUANTITY_EXCEEDS_STOCK => 'Số lượng yêu cầu vượt quá số lượng tồn kho.',
            self::MAX_ITEM_QUANTITY_REACHED => 'Bạn chỉ có thể thêm tối đa 10 sản phẩm này vào giỏ hàng.',
        };
    }
}
