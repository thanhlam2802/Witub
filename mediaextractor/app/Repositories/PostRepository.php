<?php

namespace App\Repositories;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class PostRepository extends BaseRepository
{
    /**
     * PostRepository constructor.
     *
     * @param Post $model
     */
    public function __construct(Post $model)
    {
        parent::__construct($model);
    }

    public function createWithTranslations(array $postData, array $translationsData, array $categoryIds = [], array $tagIds = [], array $relatedPostIds = [])
    {
        return DB::transaction(function () use ($postData, $translationsData, $categoryIds, $tagIds, $relatedPostIds) {
            $post = $this->create($postData);
            $post->translations()->createMany($translationsData);
            $post->categories()->sync($categoryIds);
            $post->tags()->sync($tagIds);
            $post->relatedPosts()->sync($relatedPostIds);
            return $post;
        });
    }

    public function updateWithTranslations(int $id, array $postData, array $translationsData, array $categoryIds = [], array $tagIds = [], array $relatedPostIds = [])
    {
        return DB::transaction(function () use ($id, $postData, $translationsData, $categoryIds, $tagIds, $relatedPostIds) {
            $post = $this->update($id, $postData);
            if (!$post) return null;

            foreach ($translationsData as $localeCode => $translationData) {
                $post->translations()->updateOrCreate(
                    ['locale_code' => $localeCode],
                    $translationData
                );
            }

            $post->categories()->sync($categoryIds);
            $post->tags()->sync($tagIds);
            $post->relatedPosts()->sync($relatedPostIds);

            return $post;
        });
    }

    public function searchAndPaginate(?string $search, ?array $categoryIds, int $perPage = 15, array $excludeIds = [])
    {
        $query = $this->model->query()->with(['categories.translations', 'author', 'translations',]);

        if ($search) {
            $query->whereHas('translations', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        if (!empty($categoryIds)) {
            $query->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            });
        }

        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        return $query->latest('published_at')->paginate($perPage);
    }

    public function getForCategory(Category $category, int $perPage = 10)
    {
        return $category->posts()
            ->with(['categories.translations', 'author', 'translations'])
            ->where('status', 'published')
            ->latest('published_at')
            ->paginate($perPage);
    }

    public function updateAttribute(int $id, string $attribute, $value): ?Post
    {
        $post = $this->find($id);
        if ($post) {
            $post->{$attribute} = $value;
            $post->save();
        }
        return $post;
    }


    public function getFeaturedPosts(int $limit, array $excludeIds = [], ?int $categoryId = null)
    {
        $query = Post::query()
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->where('is_featured', true)
            ->whereNotIn('id', $excludeIds);

        $query->when($categoryId, function ($q) use ($categoryId) {
            $q->whereHas('categories', function ($subQuery) use ($categoryId) {
                $subQuery->where('categories.id', $categoryId);
            });
        });

        return $query->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }



    public function getLatestPosts(int $limit, array $excludeIds = [])
    {
        return $this->model->query()
            ->with(['translations', 'categories.translations'])
            ->where('status', 'published')
            ->whereNotIn('id', $excludeIds)
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }
    /**
     * Lấy các bài viết có lượt xem cao nhất.
     *
     * @param int $limit
     * @param array $excludeIds
     * @param int|null $categoryId ID của danh mục cần lọc (hoặc null để lấy tất cả)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMostViewedPosts(int $limit, array $excludeIds = [], ?int $categoryId = null)
    {
        // Bắt đầu query
        $query = Post::query()
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->whereNotIn('id', $excludeIds);


        $query->when($categoryId, function ($q) use ($categoryId) {

            $q->whereHas('categories', function ($subQuery) use ($categoryId) {
                $subQuery->where('categories.id', $categoryId);
            });
        });


        return $query->orderByDesc('view_count')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }
    public function getRelatedPosts(Post $post, int $limit = 3)
    {
        // Lấy ID của các chuyên mục mà bài viết hiện tại thuộc về
        $categoryIds = $post->categories->pluck('id')->toArray();

        if (empty($categoryIds)) {
            return collect();
        }

        $query = $this->model->query()
            ->with(['translations', 'categories.translations'])
            ->where('status', 'published')
            // Tìm các bài viết khác có cùng chuyên mục
            ->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            })
            // Loại trừ chính bài viết hiện tại
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->limit($limit);

        return $query->get();
    }
}
