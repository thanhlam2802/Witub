<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class UpdateUserRequest extends FormRequest
{
    protected $errorBag = 'update';
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')->user_id;
        return [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $userId . ',user_id',
            'password_hash' => 'nullable|string|min:8',
            'role' => 'required|in:' . implode(',', array_keys(User::getRoleList())),
        ];
    }
}
