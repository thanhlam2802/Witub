<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // SỬA: Bỏ 'dns' để validation ổn định hơn
            'email' => ['required', 'string', 'email:rfc', 'exists:users,email']
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email không đúng định dạng.',
            'email.exists' => 'Không tìm thấy người dùng với email này.',
        ];
    }
}
