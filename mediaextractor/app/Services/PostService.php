<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Repositories\PostRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class PostService extends BaseService
{
    protected PostRepository $postRepository;
    protected ImageService $imageService;

    public function __construct(PostRepository $postRepository, ImageService $imageService)
    {
        $this->postRepository = $postRepository;
        $this->imageService = $imageService;
    }

    private function transformPostForView(Post $post, ?int $rank = null): array
    {
        $currentLocale = App::currentLocale();

        // Ưu tiên bản dịch của ngôn ngữ hiện tại, nếu không có thì fallback về tiếng Việt
        $translation = $post->translations->where('locale_code', $currentLocale)->first()
            ?? $post->translations->where('locale_code', 'vi')->first();

        if (!$translation) {
            $translation = (object) ['title' => 'Untitled', 'excerpt' => '', 'slug' => '#'];
        }


        $category = $post->categories->first();
        $categoryName = __('Uncategorized');
        if ($category) {
            $categoryTranslation = $category->translations->where('locale_code', $currentLocale)->first()
                ?? $category->translations->where('locale_code', 'vi')->first();
            if ($categoryTranslation) {
                $categoryName = $categoryTranslation->name;
            }
        }

        return [
            'title' => $translation->title,
            'excerpt' => $translation->excerpt,
            'image' => $post->thumbnail ? asset('storage/' . $post->thumbnail) : 'https://via.placeholder.com/800x600',
            'category' => $categoryName,
            'link' => localized_route('blog.resolver', ['slug' => $translation->slug]),
            'rank' => $rank,
        ];
    }

    public function getPostsForBlogIndex(int $page = 1, int $limit = 10, ?int $categoryId = null)
    {

        $featuredPost = $this->postRepository->getFeaturedPosts(1, [], $categoryId)->first();
        $excludeIds = $featuredPost ? [$featuredPost->id] : [];

        $secondaryPosts = $this->postRepository->getFeaturedPosts(3, $excludeIds, $categoryId);
        $excludeIds = array_merge($excludeIds, $secondaryPosts->pluck('id')->toArray());


        $categoryIds = $categoryId ? [$categoryId] : null;

        $mainPostsPaginator = $this->postRepository->searchAndPaginate(
            null,
            $categoryIds,
            $limit,
            $excludeIds,
            $page
        );


        $popularPosts = $this->postRepository->getMostViewedPosts(4, $excludeIds, $categoryId);

        return [
            'featuredPost' => $featuredPost ? $this->transformPostForView($featuredPost) : null,
            'secondaryPosts' => $secondaryPosts->map(fn($post) => $this->transformPostForView($post)),
            'mainPosts' => $mainPostsPaginator->getCollection()->map(fn($post) => $this->transformPostForView($post)),
            'mainPostsPagination' => $mainPostsPaginator,
            'popularPosts' => $popularPosts->map(fn($post, $key) => $this->transformPostForView($post, $key + 1)),
        ];
    }


    // /**
    //  * ✅ THAY ĐỔI: Sử dụng hàm transformPostForView để định dạng lại dữ liệu.
    //  */
    // public function getPostsByCategory(Category $category)
    // {
    //     $mainPostsPaginator = $this->postRepository->getForCategory($category, 10);
    //     $popularPosts = $this->postRepository->getFeaturedPosts(4);

    //     return [
    //         'featuredPost' => null,
    //         'secondaryPosts' => collect(),
    //         'mainPosts' => $mainPostsPaginator->getCollection()->map(fn($post) => $this->transformPostForView($post)),
    //         'mainPostsPagination' => $mainPostsPaginator,
    //         'popularPosts' => $popularPosts->map(fn($post, $key) => $this->transformPostForView($post, $key + 1)),
    //     ];
    // }


    // --- CÁC HÀM QUẢN LÝ (CREATE, UPDATE, DELETE...) GIỮ NGUYÊN ---

    public function getPosts(?string $search, ?array $categoryIds)
    {
        return $this->postRepository->searchAndPaginate($search, $categoryIds);
    }

    public function createPost(array $data)
    {
        $postData = $data['post'];
        $translationsData = $this->prepareTranslations($data['translations']);
        $categoryIds = $data['categories'] ?? [];
        $rawTags = $data['tags'] ?? [];
        $options = $data['options'] ?? [];
        $relatedPostIds = $data['related_posts'] ?? [];

        $tagIds = $this->processTags($rawTags, $translationsData);

        $this->handleThumbnailUpload($postData, $translationsData, $options);
        $this->handleSeoImageUpload($translationsData, $options);

        return $this->postRepository->createWithTranslations($postData, $translationsData, $categoryIds, $tagIds, $relatedPostIds);
    }

    public function updatePost(int $id, array $data)
    {
        $postData = $data['post'];
        $translationsData = $this->prepareTranslations($data['translations']);
        $categoryIds = $data['categories'] ?? [];
        $rawTags = $data['tags'] ?? [];
        $options = $data['options'] ?? [];
        $relatedPostIds = $data['related_posts'] ?? [];
        $tagIds = $this->processTags($rawTags, $translationsData);
        $post = $this->postRepository->find($id);

        if (!empty($options['remove_thumbnail'])) {
            $this->imageService->delete($post->thumbnail);
            $postData['thumbnail'] = null;
        } elseif (isset($postData['thumbnail']) && $postData['thumbnail'] instanceof UploadedFile) {
            $this->imageService->delete($post->thumbnail);
            $this->handleThumbnailUpload($postData, $translationsData, $options);
        } else {
            unset($postData['thumbnail']);
        }
        $this->handleSeoImageUpdate($post, $translationsData, $options);
        return $this->postRepository->updateWithTranslations($id, $postData, $translationsData, $categoryIds, $tagIds, $relatedPostIds);
    }
    private function processTags(array $rawTags, array $translationsData): array
    {
        $tagIds = [];
        foreach ($rawTags as $tagValue) {
            if (is_numeric($tagValue)) {
                $tagIds[] = (int) $tagValue;
            } else {
                $tagName = trim($tagValue);
                if (empty($tagName)) continue;
                $newTag = Tag::create([]);
                $newTag->translations()->create(['locale_code' => 'vi', 'name' => $tagName, 'slug' => Str::slug($tagName)]);
                if (isset($translationsData['en'])) {
                    $newTag->translations()->create(['locale_code' => 'en', 'name' => $tagName, 'slug' => Str::slug($tagName)]);
                }
                $tagIds[] = $newTag->id;
            }
        }
        return array_unique($tagIds);
    }

    private function handleSeoImageUpload(array &$translationsData, array $options = []): void
    {
        $convertToWebp = !empty($options['convert_to_webp']);
        foreach ($translationsData as &$translation) {
            if (isset($translation['seo_thumbnail']) && $translation['seo_thumbnail'] instanceof UploadedFile) {
                $baseName = $translation['title'] ?? 'seo-image';
                $translation['seo_thumbnail'] = $this->imageService->store($translation['seo_thumbnail'], "posts/seo", $baseName, $convertToWebp);
            }
        }
    }
    private function handleSeoImageUpdate($post, array &$translationsData, array $options = []): void
    {
        $convertToWebp = !empty($options['convert_to_webp']);
        $existingTranslations = $post->translations->keyBy('locale_code');
        foreach ($translationsData as $locale => &$translation) {
            $oldTranslation = $existingTranslations->get($locale);
            if (!empty($translation['remove_seo_thumbnail']) && $oldTranslation && $oldTranslation->seo_thumbnail) {
                $this->imageService->delete($oldTranslation->seo_thumbnail);
                $translation['seo_thumbnail'] = null;
            } elseif (isset($translation['seo_thumbnail']) && $translation['seo_thumbnail'] instanceof UploadedFile) {
                if ($oldTranslation && $oldTranslation->seo_thumbnail) {
                    $this->imageService->delete($oldTranslation->seo_thumbnail);
                }
                $baseName = $translation['title'] ?? 'seo-image';
                $translation['seo_thumbnail'] = $this->imageService->store($translation['seo_thumbnail'], "posts/seo", $baseName, $convertToWebp);
            }
        }
    }

    public function deletePost(int $id)
    {
        $post = $this->postRepository->find($id);
        if ($post) {
            if ($post->thumbnail) $this->imageService->delete($post->thumbnail);
            foreach ($post->translations as $translation) {
                if ($translation->seo_thumbnail) $this->imageService->delete($translation->seo_thumbnail);
            }
        }
        return $this->postRepository->delete($id);
    }
    private function handleThumbnailUpload(array &$postData, array $translationsData, array $options = []): void
    {
        if (!isset($postData['thumbnail']) || !$postData['thumbnail'] instanceof UploadedFile) return;
        $file = $postData['thumbnail'];
        $convertToWebp = !empty($options['convert_to_webp']);
        $baseName = current($translationsData)['title'] ?? 'post';
        $postData['thumbnail'] = $this->imageService->store($file, 'posts', $baseName, $convertToWebp);
    }

    private function prepareTranslations(array $translations): array
    {
        foreach ($translations as &$data) {
            if (empty($data['slug']) && !empty($data['title'])) {
                $data['slug'] = Str::slug($data['title']);
            }
        }
        return $translations;
    }

    public function updatePostStatus(int $id, string $attribute, $value)
    {
        return $this->postRepository->updateAttribute($id, $attribute, $value);
    }

    public function getRelatedPostsFor(Post $post)
    {
        $relatedPosts = $this->postRepository->getRelatedPosts($post, 3);

        return $relatedPosts->map(fn($p) => $this->transformPostForView($p));
    }
}
