<?php

namespace App\Enums;

enum UserErrorCode: int
{

    case INVALID_CREDENTIALS = 1001;
    case EMAIL_ALREADY_EXISTS = 1002;
    case ACCOUNT_NOT_ACTIVATED = 1003;
    case ACCOUNT_BANNED = 1004;
    case WEAK_PASSWORD = 1005;


    case USER_NOT_FOUND = 1101;
    case OLD_PASSWORD_INCORRECT = 1102;
    case CANNOT_USE_OLD_PASSWORD = 1103;

    public function message(): string
    {
        return match ($this) {

            self::INVALID_CREDENTIALS => 'Email hoặc mật khẩu không chính xác.',
            self::EMAIL_ALREADY_EXISTS => 'Địa chỉ email này đã được sử dụng.',
            self::ACCOUNT_NOT_ACTIVATED => 'Tài khoản của bạn chưa được kích hoạt.',
            self::ACCOUNT_BANNED => 'Tài khoản của bạn đã bị khóa.',
            self::WEAK_PASSWORD => 'Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường và số.',

            self::USER_NOT_FOUND => 'Không tìm thấy người dùng.',
            self::OLD_PASSWORD_INCORRECT => 'Mật khẩu cũ không chính xác.',
            self::CANNOT_USE_OLD_PASSWORD => 'Không được sử dụng lại mật khẩu cũ.',
        };
    }
}
