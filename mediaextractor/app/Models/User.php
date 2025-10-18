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


    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // ----- CÁC HÀM TIỆN ÍCH CHO ROLE -----

    public static function getRoleList(): array
    {
        return [
            self::ROLE_ADMIN => 'Quản trị viên',
            self::ROLE_POSTER => 'Người đăng bài',
            self::ROLE_USER => 'Người dùng',
        ];
    }


    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }


    public function isPoster(): bool
    {
        return $this->role === self::ROLE_POSTER;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }


    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }
}
