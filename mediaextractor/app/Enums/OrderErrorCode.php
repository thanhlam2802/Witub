<?php

namespace App\Enums;

enum OrderErrorCode: int
{

    case MISSING_SHIPPING_ADDRESS = 4001;
    case INVALID_SHIPPING_METHOD = 4002;


    case ORDER_NOT_FOUND = 4101;
    case CANNOT_CANCEL_ORDER = 4102;
    case RETURN_PERIOD_EXPIRED = 4103;

    case PAYMENT_FAILED = 4201;
    case PAYMENT_GATEWAY_UNAVAILABLE = 4202;
    case INSUFFICIENT_FUNDS = 4203;
    case CARD_DECLINED = 4204;

    public function message(): string
    {
        return match ($this) {
            self::MISSING_SHIPPING_ADDRESS => 'Vui lòng cung cấp địa chỉ giao hàng.',
            self::INVALID_SHIPPING_METHOD => 'Phương thức vận chuyển không hợp lệ cho địa chỉ của bạn.',
            self::ORDER_NOT_FOUND => 'Không tìm thấy đơn hàng.',
            self::CANNOT_CANCEL_ORDER => 'Không thể hủy đơn hàng ở trạng thái này.',
            self::RETURN_PERIOD_EXPIRED => 'Đã hết thời gian cho phép trả hàng.',
            self::PAYMENT_FAILED => 'Thanh toán thất bại. Vui lòng thử lại.',
            self::PAYMENT_GATEWAY_UNAVAILABLE => 'Cổng thanh toán hiện không khả dụng.',
            self::INSUFFICIENT_FUNDS => 'Số dư trong tài khoản không đủ.',
            self::CARD_DECLINED => 'Thẻ của bạn đã bị từ chối.',
        };
    }
}
