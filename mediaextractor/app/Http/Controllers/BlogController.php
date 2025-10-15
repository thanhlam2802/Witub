<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

use App\Services\PostService;
use Illuminate\Support\Facades\App;

class BlogController extends Controller
{
    protected PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    public function index(Request $request)
    {
        $locale = App::currentLocale();
        $blogCategories = $this->getBlogCategories($locale);

        $currentCategory = (object)[
            'id' => 0,
            'translations' => collect([(object)[
                'name' => __('blog.latest_title'),
                'slug' => __('blog.latest_slug'),
                'description' => __('blog.latest_description'),
            ]])
        ];

        $page = $request->get('page', 1);
        $limit = 10;


        $postData = $this->postService->getPostsForBlogIndex($page, $limit, null);

        if ($request->ajax()) {
            $html = '';
            foreach ($postData['mainPosts'] as $post) {
                $html .= view('components.post-card-list-item', ['post' => $post])->render();
            }
            return response()->json(['html' => $html]);
        }


        return view('blog.index', array_merge(
            [
                'categories' => $blogCategories,
                'currentCategory' => $currentCategory,
            ],
            $postData
        ));
    }
    public function resolveSlug(string $locale, string $slug, Request $request)
    {

        $category = Category::whereHas('translations', function ($query) use ($slug, $locale) {
            $query->where('slug', $slug)->where('locale_code', $locale);
        })->first();

        if ($category) {

            return $this->renderCategoryPage($locale, $category, $request);
        }


        $post = Post::whereHas('translations', function ($query) use ($slug, $locale) {
            $query->where('slug', $slug)->where('locale_code', $locale);
        })->first();

        if ($post) {

            return $this->renderPostPage($locale, $post, $request);
        }

        abort(404);
    }

    protected function renderCategoryPage(string $locale, Category $currentCategory, Request $request)
    {
        $blogCategories = $this->getBlogCategories($locale);

        $currentCategory->load(['translations' => fn($q) => $q->where('locale_code', $locale)]);

        $page = $request->get('page', 1);
        $limit = 10;

        $postData = $this->postService->getPostsForBlogIndex($page, $limit, $currentCategory->id);


        if ($request->ajax()) {
            $html = '';
            foreach ($postData['mainPosts'] as $post) {
                $html .= view('components.post-card-list-item', ['post' => $post])->render();
            }
            return response()->json(['html' => $html]);
        }

        return view('blog.index', array_merge(
            [
                'categories' => $blogCategories,
                'currentCategory' => $currentCategory,
            ],
            $postData
        ));
    }


    protected function renderPostPage(string $locale, Post $post, Request $request)
    {
        $post->load([
            'translations' => fn($q) => $q->where('locale_code', $locale),
            'categories.translations' => fn($q) => $q->where('locale_code', $locale),
            'author'
        ]);

        $relatedPosts = $this->postService->getRelatedPostsFor($post);
        $post->increment('view_count');

        return view('blog.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts
        ]);
    }


    private function getBlogCategories(string $locale)
    {
        return Category::where('type_id', 3)
            ->where('is_active', 1)
            ->with(['translations' => fn($q) => $q->where('locale_code', $locale)])
            ->orderBy('sort_order', 'asc')
            ->get();
    }
}
