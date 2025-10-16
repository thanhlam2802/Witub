<?php



namespace App\Http\Requests\Admin;


use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // app/Http/Requests/Admin/StoreSettingRequest.php

    // ...

    public function rules(): array
    {
        return [

            'footer.logo' => 'nullable|string|max:255',
            'footer.year' => 'nullable|integer',
            'footer.address' => 'nullable|string|max:255',
            'footer.hotline' => 'nullable|string|max:50',
            'footer.description' => 'nullable|string|max:500',
            'footer.email' => 'nullable|email|max:255',
            'maintenance' => 'nullable|boolean',


            'footer.menu_columns' => 'nullable|array',
            'footer.menu_columns.*.title' => 'required|string|max:100',
            'footer.menu_columns.*.items' => 'nullable|array',
            'footer.menu_columns.*.items.*.text' => 'required|string|max:100',
            'footer.menu_columns.*.items.*.url' => 'required|string|max:255',
            // ---------------------------------
        ];
    }
}
