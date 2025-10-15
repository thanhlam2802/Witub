<?php

namespace App\Enums;

enum PromoErrorCode: int
{
    case VOUCHER_NOT_FOUND = 5001;
    case VOUCHER_EXPIRED = 5002;
    case VOUCHER_LIMIT_REACHED = 5003;
    case VOUCHER_NOT_APPLICABLE = 5004;
    case MINIMUM_PURCHASE_NOT_MET = 5005;
    case VOUCHER_ALREADY_USED = 5006;

    public function message(): string
    {
        return match ($this) {
            self::VOUCHER_NOT_FOUND => 'Mã khuyến mãi không tồn tại.',
            self::VOUCHER_EXPIRED => 'Mã khuyến mãi đã hết hạn.',
            self::VOUCHER_LIMIT_REACHED => 'Mã khuyến mãi đã hết lượt sử dụng.',
            self::VOUCHER_NOT_APPLICABLE => 'Mã khuyến mãi không áp dụng cho các sản phẩm trong giỏ hàng.',
            self::MINIMUM_PURCHASE_NOT_MET => 'Chưa đạt giá trị đơn hàng tối thiểu để áp dụng mã.',
            self::VOUCHER_ALREADY_USED => 'Bạn đã sử dụng mã khuyến mãi này rồi.',
        };
    }
}
