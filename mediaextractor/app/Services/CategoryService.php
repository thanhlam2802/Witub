<?php

namespace App\Services;

use App\Repositories\CategoryRepository;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use App\Services\ImageService;

class CategoryService extends BaseService
{
    protected CategoryRepository $categoryRepository;

    protected ImageService $imageService;


    public function __construct(CategoryRepository $categoryRepository, ImageService $imageService)
    {
        $this->categoryRepository = $categoryRepository;
        $this->imageService = $imageService;
    }

    public function getCategories(?string $search = null, ?int $typeId = null)
    {

        if ($search || $typeId) {
            return $this->categoryRepository->searchAndPaginate(15, $search, $typeId);
        }


        return $this->categoryRepository->getCategoryTree();
    }

    public function createCategory(array $data)
    {
        $categoryData = $data['category'];
        $translationsData = $this->prepareTranslations($data['translations']);
        $options = $data['options'] ?? [];


        $this->handleThumbnailUpload($categoryData, $translationsData, $options);

        return $this->categoryRepository->createWithTranslations($categoryData, $translationsData);
    }
    public function updateCategory(int $id, array $data)
    {
        $categoryData = $data['category'];
        $translationsData = $this->prepareTranslations($data['translations']);
        $options = $data['options'] ?? [];

        $category = $this->categoryRepository->find($id);

        // 1. Xử lý yêu cầu xóa ảnh cũ
        if (!empty($options['remove_thumbnail'])) {
            $this->imageService->delete($category->thumbnail);
            $categoryData['thumbnail'] = null;
        } elseif (isset($categoryData['thumbnail']) && $categoryData['thumbnail'] instanceof UploadedFile) {

            $this->imageService->delete($category->thumbnail);

            $this->handleThumbnailUpload($categoryData, $translationsData, $options);
        } else {
            unset($categoryData['thumbnail']);
        }

        return $this->categoryRepository->updateWithTranslations($id, $categoryData, $translationsData);
    }

    public function deleteCategory(int $id)
    {
        $category = $this->categoryRepository->find($id);

        if ($category && $category->thumbnail) {
            $this->imageService->delete($category->thumbnail);
        }

        return $this->categoryRepository->delete($id);
    }
    private function handleThumbnailUpload(array &$categoryData, array $translationsData, array $options = []): void
    {
        if (!isset($categoryData['thumbnail']) || !$categoryData['thumbnail'] instanceof UploadedFile) {
            return;
        }

        $file = $categoryData['thumbnail'];
        $convertToWebp = !empty($options['convert_to_webp']);

        // Dùng tên của bản dịch đầu tiên làm slug cho ảnh để thân thiện với SEO
        $firstTranslation = current($translationsData);
        $baseName = $firstTranslation['name'] ?? 'category';

        // Gọi ImageService để lưu file và nhận lại đường dẫn
        $categoryData['thumbnail'] = $this->imageService->store(
            file: $file,
            folder: 'categories',
            baseName: $baseName,
            convertToWebp: $convertToWebp
        );
    }
    private function prepareTranslations(array $translations): array
    {
        foreach ($translations as $locale => &$data) {
            if (empty($data['slug']) && !empty($data['name'])) {
                $data['slug'] = Str::slug($data['name']);
            }
        }
        return $translations;
    }

    public function getCategoriesByType(string $typeCode)
    {
        return $this->categoryRepository->getCategoryTreeByType($typeCode);
    }
}
