<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Quan trọng: Import class Rule

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Cho phép mọi user đã đăng nhập (và qua middleware admin) có thể thực hiện request
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Lấy category ID từ route khi thực hiện update, nếu là store thì sẽ là null.
        $categoryId = $this->route('category') ? $this->route('category')->id : null;

        // Các quy tắc validation cơ bản
        $rules = [
            'category.parent_id' => ['nullable', 'exists:categories,id'],
            'category.type_id' => ['required', 'exists:category_types,id'],
            'category.sort_order' => ['nullable', 'integer'],
            'category.is_active' => ['nullable', 'boolean'],
            'category.thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'category.icon' => ['nullable', 'string'],
            'options' => ['nullable', 'array'],
            'options.convert_to_webp' => ['nullable', 'boolean'],
            'options.remove_thumbnail' => ['nullable', 'boolean'],

            'translations' => ['required', 'array'],
        ];

        // Thêm các quy tắc validation động cho mỗi bản dịch
        foreach ($this->input('translations', []) as $localeCode => $translation) {
            $rules["translations.{$localeCode}.name"] = ['required', 'string', 'max:255'];


            $rules["translations.{$localeCode}.slug"] = [
                'nullable',
                'string',
                'max:255',

                Rule::unique('category_translations', 'slug')

                    ->where('locale_code', $localeCode)

                    ->ignore($categoryId, 'category_id'),
            ];
            // ---------------------------------------------

            $rules["translations.{$localeCode}.description"] = ['nullable', 'string'];
            $rules["translations.{$localeCode}.seo_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$localeCode}.seo_description"] = ['nullable', 'string', 'max:500'];
            $rules["translations.{$localeCode}.seo_keywords"] = ['nullable', 'string', 'max:255'];
            $rules["translations.{$localeCode}.seo_canonical_url"] = ['nullable', 'url', 'max:255'];
        }

        return $rules;
    }

    /**
     * Chuẩn bị dữ liệu trước khi validation.
     */
    protected function prepareForValidation()
    {
        // Chuyển đổi giá trị checkbox 'is_active' thành boolean
        $this->merge([
            'category' => array_merge($this->category ?? [], [
                'is_active' => $this->input('category.is_active', false) !== false,
            ]),
        ]);
    }
}
