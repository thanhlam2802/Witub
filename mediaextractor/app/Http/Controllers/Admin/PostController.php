<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use App\Models\PostTranslation;
use Carbon\Carbon;
use App\Models\Category;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Services\CategoryService;
use App\Services\PostService;
use Illuminate\Http\Request;


class PostController extends Controller
{
    protected PostService $postService;
    protected CategoryService $categoryService;

    public function __construct(PostService $postService, CategoryService $categoryService)
    {
        $this->postService = $postService;
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        // 1. Lấy các tham số từ request
        $search = $request->query('search');

        // ✅ SỬA LỖI 1: Đọc đúng tham số 'category_id' từ URL mà form gửi lên
        $categoryId = $request->query('category_id');


        $categoryIds = [];
        if ($categoryId) {
            $categoryIds = [$categoryId];
        }

        $posts = $this->postService->getPosts($search, $categoryIds);
        $categories = $this->categoryService->getCategoriesByType('blog');


        return view('content.posts.index', compact(
            'posts',
            'categories',
            'search',
            'categoryId'
        ));
    }
    public function create()
    {

        $locales = \App\Models\Locale::where('locale_code', 'vi')->get();
        $categories = $this->categoryService->getCategoriesByType('blog');
        $tags = Tag::with('translations')->get();
        $authors = User::all();
        $allPosts = Post::with('currentTranslation')->get();

        return view('content.posts.create', compact('categories', 'tags', 'authors', 'locales', 'allPosts'));
    }

    public function edit(Post $post)
    {

        $locales = \App\Models\Locale::where('locale_code', 'vi')->get();


        $categories = $this->categoryService->getCategoriesByType('blog');

        $tags = Tag::with('translations')->get();
        $authors = User::all();
        $allPosts = Post::where('id', '!=', $post->id)->with('currentTranslation')->get();

        $post->load(['translations', 'tags', 'categories', 'relatedPosts']);

        return view('content.posts.edit', compact('post', 'categories', 'tags', 'authors', 'locales', 'allPosts'));
    }

    public function store(StorePostRequest $request)
    {
        try {
            $data = $request->validated();

            // XÓA BỎ VÒNG LẶP FOREACH Ở ĐÂY

            $this->postService->createPost($data);

            return redirect()->route('posts.index')->with('success', 'Tạo bài viết thành công.');
        } catch (\Exception $e) {
            dd($e);
        }
    }

    public function update(StorePostRequest $request, Post $post)
    {
        try {
            $data = $request->validated();



            $this->postService->updatePost($post->id, $data);

            return redirect()->route('posts.index')->with('success', 'Cập nhật bài viết thành công.');
        } catch (\Exception $e) {
            dd($e);
        }
    }

    public function destroy(Post $post)
    {
        try {
            $this->postService->deletePost($post->id);
            return redirect()->route('posts.index')->with('success', 'Xóa bài viết thành công.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function toggleStatus(Post $post, string $attribute)
    {
        try {
            $newValue = null;

            if ($attribute === 'is_featured') {
                $newValue = !$post->is_featured;
            } elseif ($attribute === 'status') {
                $newValue = $post->status === 'published' ? 'draft' : 'published';
            }

            if (!is_null($newValue)) {
                $this->postService->updatePostStatus($post->id, $attribute, $newValue);
            }

            return back()->with('success', 'Cập nhật trạng thái thành công!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }



    public function preview(Request $request)
    {
        // === BƯỚC 1: TẠO MODEL "ẢO" TỪ DỮ LIỆU FORM ===
        $post = Post::make($request->input('post', []));

        // Gán các giá trị mặc định để view không bị lỗi
        $post->id = 0;
        $post->created_at = Carbon::now();
        $post->view_count = 0;

        // === CẢI TIẾN LOGIC XỬ LÝ THUMBNAIL ===
        if ($request->hasFile('post.thumbnail')) {
            // Trường hợp 1: Người dùng tải lên một ảnh MỚI
            $file = $request->file('post.thumbnail');
            $post->thumbnail = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file));
        } else if ($request->filled('post.existing_thumbnail')) {
            // Trường hợp 2: Người dùng KHÔNG tải ảnh mới, sử dụng ảnh cũ từ input ẩn
            $post->thumbnail = $request->input('post.existing_thumbnail');
        }

        // === BƯỚC 2: MÔ PHỎNG VIỆC LOAD CÁC MỐI QUAN HỆ (Giữ nguyên) ===
        $locale = 'vi'; // Giả sử chỉ xem trước tiếng Việt

        // ... (phần còn lại của hàm giữ nguyên)
        // 1. Gắn quan hệ "translations"
        $translationData = $request->input("translations.{$locale}", []);
        $translation = PostTranslation::make($translationData);
        $post->setRelation('translations', collect([$translation]));

        // 2. Gắn quan hệ "categories"
        $categoryIds = $request->input('categories', []);
        if (!empty($categoryIds)) {
            // Lấy danh mục thật từ DB để hiển thị tên chính xác
            $categories = Category::whereIn('id', $categoryIds)
                ->with(['translations' => fn($q) => $q->where('locale_code', $locale)])
                ->get();
            $post->setRelation('categories', $categories);
        } else {
            // Luôn đảm bảo `categories` là một collection để tránh lỗi
            $post->setRelation('categories', collect([]));
        }

        // 3. Gắn quan hệ "author"
        $authorId = $request->input('post.author_id');
        if ($authorId && $author = User::find($authorId)) {
            $post->setRelation('author', $author);
        }

        // === BƯỚC 3: LẤY DỮ LIỆU BÀI VIẾT LIÊN QUAN ===
        $relatedPostIds = $request->input('related_posts', []);
        $relatedPosts = !empty($relatedPostIds)
            ? Post::with(['translations' => fn($q) => $q->where('locale_code', $locale)])->findMany($relatedPostIds)
            : collect([]);


        return view('blog.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts
        ]);
    }
}
