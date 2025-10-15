<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'post.author_id' => ['required', 'exists:users,user_id'],

            'post.status' => ['required', 'in:draft,published,pending_review'],
            'post.is_featured' => ['nullable', 'boolean'],
            'post.is_indexable' => ['required', 'boolean'],
            'post.published_at' => ['nullable', 'date'],
            'post.thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'options.convert_to_webp' => ['nullable', 'boolean'],
            'options.remove_thumbnail' => ['nullable', 'boolean'],
            'categories' => 'required|array|min:1',
            'categories.*' => ['exists:categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
            'translations' => ['required', 'array'],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['nullable', 'string', 'max:255'],
            'translations.*.excerpt' => ['nullable', 'string'],
            'translations.*.content' => ['required', 'string'],
            'translations.*.seo_title' => ['nullable', 'string', 'max:255'],
            'translations.*.seo_description' => ['nullable', 'string', 'max:170'],
            'translations.*.seo_keywords' => ['nullable', 'string', 'max:255'],
            'translations.*.seo_canonical_url' => ['nullable', 'url', 'max:255'],
            'translations.*.seo_thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'translations.*.remove_seo_thumbnail' => ['nullable', 'boolean'],
            'related_posts' => ['nullable', 'array'],
            'related_posts.*' => ['exists:posts,id'],
        ];
    }
}
