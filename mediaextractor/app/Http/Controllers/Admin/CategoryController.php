<?php

namespace App\Http\Controllers\Admin;

use App\Repositories\CategoryTypeRepository;
use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;

use App\Models\Category;
use App\Services\CategoryService;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Models\CategoryType;
use App\Models\Locale;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;
    protected CategoryTypeRepository $categoryTypeRepository;


    public function __construct(
        CategoryService $categoryService,
        CategoryTypeRepository $categoryTypeRepository
    ) {
        $this->categoryService = $categoryService;
        $this->categoryTypeRepository = $categoryTypeRepository;
    }

    public function index(Request $request)
    {

        $search = $request->query('search');
        $typeFilter = $request->query('type');
        $categories = $this->categoryService->getCategories($search, $typeFilter);
        $types = $this->categoryTypeRepository->getAllActive();
        $is_search = !empty($search) || !empty($typeFilter);


        if ($categories->isNotEmpty()) {

            $categories->load('translations', 'type');
        }

        return view('content.categories.index', compact('categories', 'search', 'is_search', 'types', 'typeFilter'));
    }

    public function create(Request $request)
    {
        $locales = Locale::where('is_active', true)->get();
        $types = CategoryType::where('is_active', true)->get();


        $selectedTypeId = $request->query('type');

        $parentCategories = $this->categoryService->getCategories(null, $selectedTypeId);

        return view('content.categories.create', compact('locales', 'types', 'parentCategories', 'selectedTypeId'));
    }

    public function store(StoreCategoryRequest $request)
    {

        try {
            $this->categoryService->createCategory($request->validated());
            return redirect()->route('categories.index')->with('success', 'Tạo danh mục thành công.');
        } catch (BusinessException | ApiException $e) {

            return back()->with('error', $e->getMessage())->withInput();
        }
    }



    public function edit(Category $category)
    {
        $locales = Locale::where('is_active', true)->get();
        $types = CategoryType::where('is_active', true)->get();


        $parentCategories = $this->categoryService->getCategories(null, $category->type_id);

        $category->load('translations');
        $translations = $category->translations->keyBy('locale_code');

        return view('content.categories.edit', compact('category', 'locales', 'types', 'parentCategories', 'translations'));
    }


    public function update(StoreCategoryRequest $request, Category $category)
    {

        try {
            $this->categoryService->updateCategory($category->id, $request->validated());
            return redirect()->route('categories.index')->with('success', 'Cập nhật danh mục thành công.');
        } catch (BusinessException | ApiException $e) {

            return back()->with('error', $e->getMessage())->withInput();
        }
    }
    public function destroy(Category $category)
    {

        try {
            $this->categoryService->deleteCategory($category->id);
            return redirect()->route('categories.index')->with('success', 'Xóa danh mục thành công.');
        } catch (BusinessException | ApiException $e) {

            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
