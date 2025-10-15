<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
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
        return [
            'search' => [
                'nullable',
                'string',
                'max:255'
            ],
            'page' => [
                'nullable',
                'integer',
                'min:1',
                'max:1000'
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100'
            ],
            'sort_by' => [
                'nullable',
                'string',
                'max:50'
            ],
            'sort_direction' => [
                'nullable',
                'string',
                'in:asc,desc'
            ],
            'filters' => [
                'nullable',
                'array'
            ],
            'filters.*' => [
                'string',
                'max:100'
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
            'search.max' => 'Từ khóa tìm kiếm không được vượt quá 255 ký tự.',
            
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn 0.',
            'page.max' => 'Trang không được vượt quá 1000.',
            
            'per_page.integer' => 'Số lượng mỗi trang phải là số nguyên.',
            'per_page.min' => 'Số lượng mỗi trang phải lớn hơn 0.',
            'per_page.max' => 'Số lượng mỗi trang không được vượt quá 100.',
            
            'sort_by.max' => 'Trường sắp xếp không được vượt quá 50 ký tự.',
            
            'sort_direction.in' => 'Hướng sắp xếp phải là asc hoặc desc.',
            
            'filters.array' => 'Bộ lọc phải là mảng.',
            'filters.*.max' => 'Mỗi bộ lọc không được vượt quá 100 ký tự.'
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
            'search' => 'từ khóa tìm kiếm',
            'page' => 'trang',
            'per_page' => 'số lượng mỗi trang',
            'sort_by' => 'trường sắp xếp',
            'sort_direction' => 'hướng sắp xếp',
            'filters' => 'bộ lọc'
        ];
    }
}
