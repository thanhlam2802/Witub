<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user() ? $this->user()->id : null;
        
        return [
            'name' => [
                'sometimes',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-ZÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂÂÊÔƠưăâêôơ\s]+$/'
            ],
            'email' => [
                'sometimes',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:users,email,' . $userId
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^[0-9]{10,11}$/'
            ],
            'date_of_birth' => [
                'nullable',
                'date',
                'before:today',
                'after:1900-01-01'
            ],
            'gender' => [
                'nullable',
                'string',
                'in:male,female,other'
            ],
            'address' => [
                'nullable',
                'string',
                'max:500'
            ],
            'city' => [
                'nullable',
                'string',
                'max:100'
            ],
            'province' => [
                'nullable',
                'string',
                'max:100'
            ],
            'postal_code' => [
                'nullable',
                'string',
                'regex:/^[0-9]{5,6}$/'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'name.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'name.regex' => 'Họ và tên chỉ được chứa chữ cái và khoảng trắng.',
            
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng.',
            
            'phone.regex' => 'Số điện thoại phải có 10-11 chữ số.',
            
            'date_of_birth.date' => 'Ngày sinh không đúng định dạng.',
            'date_of_birth.before' => 'Ngày sinh phải trước ngày hiện tại.',
            'date_of_birth.after' => 'Ngày sinh không hợp lệ.',
            
            'gender.in' => 'Giới tính không hợp lệ.',
            
            'address.max' => 'Địa chỉ không được vượt quá 500 ký tự.',
            'city.max' => 'Thành phố không được vượt quá 100 ký tự.',
            'province.max' => 'Tỉnh/thành không được vượt quá 100 ký tự.',
            'postal_code.regex' => 'Mã bưu điện phải có 5-6 chữ số.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'họ và tên',
            'email' => 'email',
            'phone' => 'số điện thoại',
            'date_of_birth' => 'ngày sinh',
            'gender' => 'giới tính',
            'address' => 'địa chỉ',
            'city' => 'thành phố',
            'province' => 'tỉnh/thành',
            'postal_code' => 'mã bưu điện'
        ];
    }
}
