<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Services\CategoryTypeService;
use App\Models\CategoryType;

class CategoryTypeController extends Controller
{
    protected CategoryTypeService $categoryTypeService;

    public function __construct(CategoryTypeService $categoryTypeService)
    {
        $this->categoryTypeService = $categoryTypeService;
    }

    /**
     * Hiển thị danh sách các loại danh mục (type)
     */
    public function index(): View
    {
        $categoryTypes = $this->categoryTypeService->getAllActive();
        return view('content.category-types.index', compact('categoryTypes'));
    }

    /**
     * Form tạo mới
     */
    public function create(): View
    {
        return view('content.category-types.create');
    }


    public function store(Request $request): RedirectResponse
    {
        $request->merge(['is_active' => $request->has('is_active')]);

        $validated = $request->validate([
            // Thêm rule cho 'code'
            'code' => 'required|string|max:255|unique:category_types,code',
            'name' => 'required|string|max:255|unique:category_types,name',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $this->categoryTypeService->create($validated);

        return redirect()->route('category-types.index')->with('success', 'Category type created successfully.');
    }


    public function update(Request $request, CategoryType $categoryType): RedirectResponse
    {
        $request->merge(['is_active' => $request->has('is_active')]);

        $validated = $request->validate([

            'code' => 'required|string|max:255|unique:category_types,code,' . $categoryType->id,
            'name' => 'required|string|max:255|unique:category_types,name,' . $categoryType->id,
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $this->categoryTypeService->update($categoryType, $validated);

        return redirect()->route('category-types.index')->with('success', 'Category type updated successfully.');
    }


    /**
     * Form chỉnh sửa
     */
    public function edit(CategoryType $categoryType): View
    {
        return view('content.category-types.edit', compact('categoryType'));
    }




    /**
     * Xóa loại danh mục
     */
    public function destroy(CategoryType $categoryType): RedirectResponse
    {
        $this->categoryTypeService->delete($categoryType);

        return redirect()
            ->route('category-types.index')
            ->with('success', 'Category type deleted successfully.');
    }
}
