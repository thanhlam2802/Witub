<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Locale;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Hiển thị danh sách các ngôn ngữ.
     */
    public function index()
    {
        $locales = Locale::orderBy('is_default', 'desc')->get();
        return view('content.locales.index', compact('locales'));
    }

    /**
     * Hiển thị form tạo mới ngôn ngữ.
     */
    public function create()
    {
        return view('content.locales.create');
    }

    /**
     * Lưu ngôn ngữ mới vào database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'locale_code' => 'required|string|max:5|unique:locales,locale_code',
            'language_name' => 'required|string|max:50',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        // Xử lý logic cho is_default
        if (isset($validated['is_default']) && $validated['is_default']) {
            Locale::where('is_default', true)->update(['is_default' => false]);
        }

        Locale::create($validated);

        return redirect()->route('locales.index')->with('success', 'Thêm ngôn ngữ thành công.');
    }

    /**
     * Hiển thị form chỉnh sửa ngôn ngữ.
     */
    public function edit(Locale $locale)
    {
        return view('content.locales.edit', compact('locale'));
    }

    /**
     * Cập nhật thông tin ngôn ngữ.
     */
    public function update(Request $request, Locale $locale)
    {
        $validated = $request->validate([
            'language_name' => 'required|string|max:50',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        // Xử lý logic cho is_default
        if (isset($validated['is_default']) && $validated['is_default']) {
            Locale::where('is_default', true)->where('locale_code', '!=', $locale->locale_code)->update(['is_default' => false]);
        }

        $locale->update($validated);

        return redirect()->route('locales.index')->with('success', 'Cập nhật ngôn ngữ thành công.');
    }

    /**
     * Xóa ngôn ngữ.
     */
    public function destroy(Locale $locale)
    {
        if ($locale->is_default) {
            return back()->with('error', 'Không thể xóa ngôn ngữ mặc định.');
        }



        $locale->delete();

        return redirect()->route('locales.index')->with('success', 'Xóa ngôn ngữ thành công.');
    }
}
