<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class StoreUserRequest extends FormRequest
{
    protected $errorBag = 'store';
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password_hash' => 'required|string|min:8',
            'role' => 'required|in:' . implode(',', array_keys(User::getRoleList())),
        ];
    }
}
