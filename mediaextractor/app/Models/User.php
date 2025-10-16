<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{

    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public const UPDATED_AT = null;
    public const CREATED_AT = 'created_at';

    // ----- PHẦN BỔ SUNG CHO ROLE -----

    /**
     * Định nghĩa các hằng số cho vai trò.
     * Giúp code an toàn hơn, tránh lỗi chính tả khi gõ chuỗi.
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_POSTER = 'poster';
    public const ROLE_USER = 'user';

    // ------------------------------------
    protected $dates = ['email_verified_at'];
    protected $fillable = [
        'full_name',
        'email',
        'email_verified_at',
        'password_hash',
        'role',
        'preferred_locale',
        'is_active',
        'avatar',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_hash' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Ghi đè phương thức để Laravel biết tên cột mật khẩu của bạn là 'password_hash'.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // ----- CÁC HÀM TIỆN ÍCH CHO ROLE -----

    /**
     * Lấy danh sách các vai trò và tên hiển thị tương ứng.
     * Rất hữu ích khi dùng cho form select/dropdown.
     *
     * @return array
     */
    public static function getRoleList(): array
    {
        return [
            self::ROLE_ADMIN => 'Quản trị viên',
            self::ROLE_POSTER => 'Người đăng bài',
            self::ROLE_USER => 'Người dùng',
        ];
    }

    /**
     * Kiểm tra người dùng có phải là Admin không.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Kiểm tra người dùng có phải là Người đăng bài không.
     *
     * @return bool
     */
    public function isPoster(): bool
    {
        return $this->role === self::ROLE_POSTER;
    }
    /**
     * THÊM HÀM NÀY VÀO
     *
     * Kiểm tra user có đang hoạt động không.
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Kiểm tra người dùng có phải là Người dùng thông thường không.
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }
}
