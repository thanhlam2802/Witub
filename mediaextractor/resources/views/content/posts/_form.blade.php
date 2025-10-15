@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.default.min.css" rel="stylesheet">
@endpush

<form action="{{ isset($post) ? route('posts.update', $post->id) : route('posts.store') }}" method="POST"
    enctype="multipart/form-data" class="p-4" id="post-form">
    @if ($errors->any())
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-100" role="alert">
            <span class="font-bold">Có lỗi xảy ra với dữ liệu bạn nhập:</span>
            <ul class="mt-2 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-100" role="alert">
            <span class="font-bold">Lỗi hệ thống:</span> {{ session('error') }}
        </div>
    @endif
    @csrf
    @if (isset($post))
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- === CỘT BÊN TRÁI === --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="p-4 bg-white border rounded-lg shadow-sm dark:bg-gray-800">
                <h3 class="text-lg font-semibold mb-4">Thumbnail</h3>
                <div class="relative w-full h-48 mb-2 border rounded-lg flex items-center justify-center">
                    <div id="existing-thumbnail-container"
                        class="{{ isset($post) && $post->thumbnail ? '' : 'hidden' }}">
                        @if (isset($post) && $post->thumbnail)
                            <img src="{{ asset('storage/' . $post->thumbnail) }}" alt="Current Thumbnail"
                                class="h-48 w-full object-cover rounded-lg">
                        @endif
                        <input type="hidden" name="post[existing_thumbnail]" value="{{ $post->thumbnail }}">
                    </div>
                    <img src="" alt="New Thumbnail Preview" id="thumbnail-preview"
                        class="h-48 w-full object-cover rounded-lg hidden">
                    <button type="button" id="remove-thumbnail-button"
                        class="absolute top-1 right-1 bg-white rounded-full p-1 leading-none text-gray-700 hover:bg-gray-200 hidden">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
                <input type="hidden" name="options[remove_thumbnail]" id="remove-thumbnail-input" value="0">
                <input type="file" name="post[thumbnail]" id="thumbnail-input"
                    class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full"
                    accept="image/*">
                <div class="flex items-center mt-2">
                    <input id="convert_to_webp" name="options[convert_to_webp]" type="checkbox" value="1"
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded">
                    <label for="convert_to_webp" class="ml-2 text-sm font-medium">Chuyển đổi sang .webp</label>
                </div>
            </div>

            <div class="p-4 bg-white border rounded-lg shadow-sm dark:bg-gray-800">
                <h3 class="text-lg font-semibold mb-4">Publish</h3>
                <div class="space-y-4">
                    <div>
                        <label for="author_id" class="block mb-2 text-sm font-medium">Author</label>
                        <select name="post[author_id]" id="author_id"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5"
                            required>
                            @foreach ($authors as $author)
                                <option value="{{ $author->user_id }}" @selected(old('post.author_id', $post->author_id ?? auth()->id()) == $author->user_id)>
                                    {{ $author->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status" class="block mb-2 text-sm font-medium">Status</label>
                        <select name="post[status]" id="status"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5">
                            <option value="published" @selected(old('post.status', $post->status ?? 'published') == 'published')>Published</option>
                            <option value="draft" @selected(old('post.status', $post->status ?? 'published') == 'draft')>Draft</option>
                        </select>
                    </div>
                    <div class="flex items-center">
                        <input type="hidden" name="post[is_featured]" value="0">
                        <input type="checkbox" name="post[is_featured]" value="1" id="is_featured"
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded"
                            @checked(old('post.is_featured', $post?->is_featured ?? false))>
                        <label for="is_featured" class="ml-2 text-sm font-medium">Is Featured?</label>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-white border rounded-lg shadow-sm dark:bg-gray-800">
                <h3 class="text-lg font-semibold mb-4">Categories</h3>
                <ul class="space-y-2 max-h-60 overflow-y-auto border p-2 rounded-md">
                    @forelse ($categories as $categoryItem)
                        @include('content.categories._category-checkbox', [
                            'categoryItem' => $categoryItem,
                            'level' => 0,
                            'post' => $post ?? null,
                        ])
                    @empty
                        <li>Không tìm thấy danh mục cho loại "Bài viết".</li>
                    @endforelse
                </ul>


                @if ($errors->has('categories'))
                    <p class="text-red-600 text-sm mt-2">{{ $errors->first('categories') }}</p>
                @endif
            </div>


            <div class="p-4 bg-white border rounded-lg shadow-sm dark:bg-gray-800">
                <h3 class="text-lg font-semibold mb-4">Tags</h3>
                <select id="tags-select" name="tags[]" multiple>
                    @foreach ($tags as $tag)
                        <option value="{{ $tag->id }}" @selected(in_array($tag->id, old('tags', isset($post) ? $post->tags->pluck('id')->toArray() : [])))>
                            {{ $tag->translations->firstWhere('locale_code', 'vi')?->name ?? $tag->id }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="p-4 bg-white border rounded-lg shadow-sm dark:bg-gray-800">
                <h3 class="text-lg font-semibold mb-4">Bài viết liên quan</h3>
                <select id="related-posts-select" name="related_posts[]" multiple>
                    @foreach ($allPosts as $relatedPost)
                        <option value="{{ $relatedPost->id }}" @selected(in_array($relatedPost->id, old('related_posts', isset($post) ? $post->relatedPosts->pluck('id')->toArray() : [])))>
                            {{ $relatedPost->currentTranslation?->title ?? 'Post ID: ' . $relatedPost->id }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- === CỘT BÊN PHẢI (NỘI DUNG CHÍNH) === --}}
        <div class="lg:col-span-2">
            <div class="bg-white border rounded-lg shadow-sm dark:bg-gray-800">
                @php
                    $locale = $locales->firstWhere('locale_code', 'vi') ?? $locales->first();
                @endphp

                <div class="border-b">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                        <li class="mr-2">
                            <button type="button"
                                class="inline-block p-4 border-b-2 rounded-t-lg text-blue-600 border-blue-600"
                                data-tab-toggle="#content-panel">
                                Nội dung chính
                            </button>
                        </li>
                        <li class="mr-2">
                            <button type="button"
                                class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600"
                                data-tab-toggle="#seo-panel">
                                SEO
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="p-4">
                    @if ($locale)
                        @php
                            $translation = isset($post)
                                ? $post->translations->firstWhere('locale_code', $locale->locale_code)
                                : null;
                            $langCode = strtoupper($locale->locale_code);
                        @endphp

                        <div class="space-y-4" id="content-panel" role="tabpanel">
                            <div>
                                <label for="title-{{ $locale->locale_code }}"
                                    class="block mb-2 text-sm font-medium">Title ({{ $langCode }})</label>
                                <input type="text" id="title-{{ $locale->locale_code }}"
                                    name="translations[{{ $locale->locale_code }}][title]"
                                    value="{{ old('translations.' . $locale->locale_code . '.title', $translation?->title ?? '') }}"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5"
                                    required>
                            </div>
                            <div>
                                <label for="slug-{{ $locale->locale_code }}"
                                    class="block mb-2 text-sm font-medium">Slug ({{ $langCode }})</label>
                                <input type="text" id="slug-{{ $locale->locale_code }}"
                                    name="translations[{{ $locale->locale_code }}][slug]"
                                    value="{{ old('translations.' . $locale->locale_code . '.slug', $translation?->slug ?? '') }}"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5">
                            </div>
                            <div>
                                <label for="excerpt-{{ $locale->locale_code }}"
                                    class="block mb-2 text-sm font-medium">Excerpt ({{ $langCode }})</label>
                                <textarea name="translations[{{ $locale->locale_code }}][excerpt]" rows="3"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5">{{ old('translations.' . $locale->locale_code . '.excerpt', $translation?->excerpt ?? '') }}</textarea>
                            </div>
                            <div>
                                <label for="content-{{ $locale->locale_code }}"
                                    class="block mb-2 text-sm font-medium">Content ({{ $langCode }})</label>
                                <textarea name="translations[{{ $locale->locale_code }}][content]" rows="10"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5">{{ old('translations.' . $locale->locale_code . '.content', $translation?->content ?? '') }}</textarea>

                                <div class="mt-4 flex items-center space-x-2">
                                    <button type="button" id="insert-post-button"
                                        class="inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                        Chèn bài viết vào nội dung
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 hidden" id="seo-panel" role="tabpanel">
                            <h4 class="text-md font-semibold mb-3">SEO Fields ({{ $langCode }})</h4>

                            <div class="border rounded-lg p-4 mb-4 bg-gray-50">
                                <h5 class="text-sm font-semibold mb-2">Bản xem trước Google</h5>
                                <div id="google-preview-{{ $locale->locale_code }}"
                                    class="border rounded-lg p-3 bg-white">
                                    @php
                                        $previewThumbnail = $translation?->seo_thumbnail
                                            ? asset('storage/' . $translation->seo_thumbnail)
                                            : (isset($post->thumbnail)
                                                ? asset('storage/' . $post->thumbnail)
                                                : null);
                                    @endphp
                                    <div id="google-preview-image-container-{{ $locale->locale_code }}"
                                        class="{{ $previewThumbnail ? '' : 'hidden' }} mb-3">
                                        <img src="{{ $previewThumbnail ?? '' }}" alt="SEO Thumbnail preview"
                                            class="w-full max-h-48 object-cover rounded-md">
                                    </div>
                                    <div id="google-preview-placeholder-{{ $locale->locale_code }}"
                                        class="{{ $previewThumbnail ? 'hidden' : '' }} w-full h-32 bg-gray-200 flex items-center justify-center text-gray-500 rounded-md mb-3">
                                        <span>Chưa có ảnh thumbnail</span>
                                    </div>
                                    <p id="google-title-{{ $locale->locale_code }}"
                                        class="text-blue-700 text-lg truncate font-medium">
                                        {{ old('translations.' . $locale->locale_code . '.seo_title', $translation?->seo_title ?? 'Tiêu đề bài viết') }}
                                    </p>
                                    <p class="text-green-700 text-sm truncate">https://yourdomain.com/example-url</p>
                                    <p id="google-description-{{ $locale->locale_code }}"
                                        class="text-gray-700 text-sm mt-1">
                                        {{ old('translations.' . $locale->locale_code . '.seo_description', $translation?->seo_description ?? 'Mô tả bài viết...') }}
                                    </p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Ảnh SEO Thumbnail</label>
                                <input type="hidden"
                                    name="translations[{{ $locale->locale_code }}][remove_seo_thumbnail]"
                                    id="remove-seo-thumbnail-input-{{ $locale->locale_code }}" value="0">
                                <input type="file" id="seo-thumbnail-input-{{ $locale->locale_code }}"
                                    name="translations[{{ $locale->locale_code }}][seo_thumbnail]" accept="image/*"
                                    class="block w-full text-sm text-gray-700 border rounded-md p-2 cursor-pointer">

                                <div class="mt-2 relative w-48 h-32"
                                    id="seo-thumbnail-wrapper-{{ $locale->locale_code }}">
                                    @php
                                        $existingSeoThumbnail = $translation?->seo_thumbnail
                                            ? asset('storage/' . $translation->seo_thumbnail)
                                            : null;
                                    @endphp
                                    <div id="existing-seo-thumbnail-container-{{ $locale->locale_code }}"
                                        class="{{ $existingSeoThumbnail ? '' : 'hidden' }}">
                                        <img src="{{ $existingSeoThumbnail ?? '' }}" alt="Current SEO Thumbnail"
                                            class="w-48 h-32 object-cover rounded-md border">
                                    </div>
                                    <img src="" alt="New SEO Thumbnail"
                                        id="seo-thumbnail-preview-{{ $locale->locale_code }}"
                                        class="w-48 h-32 object-cover rounded-md border hidden">
                                    <div id="seo-thumbnail-placeholder-{{ $locale->locale_code }}"
                                        class="{{ $existingSeoThumbnail ? 'hidden' : '' }} w-48 h-32 bg-gray-200 flex items-center justify-center text-gray-500 rounded-md">
                                        <span>Chưa có ảnh</span>
                                    </div>
                                    <button type="button"
                                        id="remove-seo-thumbnail-button-{{ $locale->locale_code }}"
                                        class="{{ $existingSeoThumbnail ? '' : 'hidden' }} absolute top-1 right-1 bg-white rounded-full p-1 leading-none text-gray-700 hover:bg-gray-200">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label for="seo_title-{{ $locale->locale_code }}"
                                        class="block mb-2 text-sm font-medium">SEO Title ({{ $langCode }})</label>
                                    <div class="relative">
                                        <input type="text" id="seo_title-{{ $locale->locale_code }}"
                                            name="translations[{{ $locale->locale_code }}][seo_title]" maxlength="70"
                                            value="{{ old('translations.' . $locale->locale_code . '.seo_title', $translation?->seo_title ?? '') }}"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5">
                                        <div class="absolute bottom-0 left-0 h-1 bg-gradient-to-r from-blue-400 to-blue-600 transition-all duration-300"
                                            id="title-progress-{{ $locale->locale_code }}" style="width:0%;"></div>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-1"><span
                                            id="title-count-{{ $locale->locale_code }}">0</span> / 70 ký tự</p>
                                </div>
                                <div>
                                    <label for="seo_description-{{ $locale->locale_code }}"
                                        class="block mb-2 text-sm font-medium">SEO Description
                                        ({{ $langCode }})</label>
                                    <div class="relative">
                                        <textarea name="translations[{{ $locale->locale_code }}][seo_description]"
                                            id="seo_description-{{ $locale->locale_code }}" rows="3" maxlength="170"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5">{{ old('translations.' . $locale->locale_code . '.seo_description', $translation?->seo_description ?? '') }}</textarea>
                                        <div class="absolute bottom-0 left-0 h-1 bg-gradient-to-r from-green-400 to-green-600 transition-all duration-300"
                                            id="desc-progress-{{ $locale->locale_code }}" style="width:0%;"></div>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-1"><span
                                            id="desc-count-{{ $locale->locale_code }}">0</span> / 170 ký tự</p>
                                </div>
                                <div>
                                    <label for="seo_keywords-{{ $locale->locale_code }}"
                                        class="block mb-2 text-sm font-medium">SEO Keywords
                                        ({{ $langCode }})</label>
                                    <input type="text" id="seo_keywords-{{ $locale->locale_code }}"
                                        name="translations[{{ $locale->locale_code }}][seo_keywords]"
                                        value="{{ old('translations.' . $locale->locale_code . '.seo_keywords', $translation?->seo_keywords ?? '') }}"
                                        placeholder="laravel, php, javascript"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5">
                                    <p class="mt-1 text-xs text-gray-500">Các từ khóa cách nhau bằng dấu phẩy.</p>
                                </div>
                                <div>
                                    <label for="seo_canonical_url-{{ $locale->locale_code }}"
                                        class="block mb-2 text-sm font-medium">Canonical URL
                                        ({{ $langCode }})</label>
                                    <input type="url" id="seo_canonical_url-{{ $locale->locale_code }}"
                                        name="translations[{{ $locale->locale_code }}][seo_canonical_url]"
                                        value="{{ old('translations.' . $locale->locale_code . '.seo_canonical_url', $translation?->seo_canonical_url ?? '') }}"
                                        placeholder="https://yourdomain.com/original-post"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5">
                                    <p class="mt-1 text-xs text-gray-500">URL gốc của bài viết nếu đây là bản sao.</p>
                                </div>

                                {{-- === START: GOOGLE INDEX SECTION === --}}
                                <div class="pt-4 mt-4 border-t">
                                    <label class="block text-sm font-medium mb-2">Chỉ mục tìm kiếm (Google
                                        Index)</label>
                                    <div class="flex items-center">
                                        {{-- This hidden input ensures a value of 0 is sent when the checkbox is unchecked --}}
                                        <input type="hidden" name="post[is_indexable]" value="0">
                                        <input type="checkbox" name="post[is_indexable]" value="1"
                                            id="is_indexable"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                            @checked(old('post.is_indexable', $post->is_indexable ?? true))>
                                        <label for="is_indexable"
                                            class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">
                                            Cho phép các công cụ tìm kiếm index bài viết này (index, follow)
                                        </label>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">Bỏ chọn mục này sẽ thêm thẻ "noindex,
                                        nofollow" vào bài viết, ngăn Google index nội dung.</p>
                                </div>
                                {{-- === END: GOOGLE INDEX SECTION === --}}
                            </div>
                        </div>
                    @else
                        <p class="text-red-500">Lỗi: Không tìm thấy ngôn ngữ mặc định để hiển thị form.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="mt-6">
        <button type="button" id="preview-button"
            class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 text-center mr-2">
            Xem thử
        </button>
        <button type="submit"
            class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
            {{ isset($post) ? 'Update Post' : 'Save Post' }}
        </button>
    </div>
</form>

<div id="post-selection-modal"
    class="fixed inset-0 bg-gray-800 bg-opacity-75 overflow-y-auto h-full w-full hidden z-50 transition-opacity">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Chọn một bài viết để chèn</h3>
            <button id="modal-close-button" type="button" class="text-gray-400 hover:text-gray-600">
                <span class="text-2xl">&times;</span>
            </button>
        </div>
        <div class="mt-4">
            <input type="text" id="modal-post-search" placeholder="Tìm kiếm bài viết..."
                class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <ul id="modal-post-list" class="mt-3 max-h-72 overflow-y-auto divide-y divide-gray-200">
            </ul>
        </div>
    </div>
</div>


@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/eq58jh4jrdr6tqynh2dg8gy8m8za5og3h205zfp9qz82jh4d/tinymce/7/tinymce.min.js"
        referrerpolicy="origin"></script>

    <script>
        const allPostsForModal = @json($allPosts->map(fn($p) => ['id' => $p->id, 'title' => $p->currentTranslation?->title ?? 'Post ID: ' . $p->id]));
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const slugify = (text) => {
                return text.toString().toLowerCase()
                    .replace(/\s+/g, '-')
                    .replace(/[^\w\-]+/g, '')
                    .replace(/\-\-+/g, '-')
                    .replace(/^-+/, '')
                    .replace(/-+$/, '');
            };

            const setupTabs = () => {
                const tabButtons = document.querySelectorAll('[data-tab-toggle]');
                const tabPanels = document.querySelectorAll('[role="tabpanel"]');

                tabButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        tabButtons.forEach(btn => {
                            btn.classList.remove('text-blue-600', 'border-blue-600');
                            btn.classList.add('hover:text-gray-600');
                        });
                        button.classList.add('text-blue-600', 'border-blue-600');
                        button.classList.remove('hover:text-gray-600');

                        tabPanels.forEach(panel => {
                            panel.classList.add('hidden');
                        });
                        const targetPanel = document.querySelector(button.dataset.tabToggle);
                        if (targetPanel) {
                            targetPanel.classList.remove('hidden');
                        }
                    });
                });
            };

            const setupThumbnailManager = () => {
                const input = document.getElementById('thumbnail-input');
                const preview = document.getElementById('thumbnail-preview');
                const existingContainer = document.getElementById('existing-thumbnail-container');
                const removeButton = document.getElementById('remove-thumbnail-button');
                const removeInput = document.getElementById('remove-thumbnail-input');
                if (!input) return;

                if (existingContainer && !existingContainer.classList.contains('hidden')) {
                    removeButton.classList.remove('hidden');
                }
                input.addEventListener('change', (event) => {
                    const file = event.target.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                        removeButton.classList.remove('hidden');
                        if (existingContainer) existingContainer.classList.add('hidden');
                        removeInput.value = '0';
                    };
                    reader.readAsDataURL(file);
                });
                removeButton.addEventListener('click', () => {
                    input.value = null;
                    preview.src = '';
                    preview.classList.add('hidden');
                    removeButton.classList.add('hidden');
                    if (existingContainer) existingContainer.classList.add('hidden');
                    removeInput.value = '1';
                });
            };

            const setupSlugGenerator = () => {
                const titleInput = document.querySelector('input[id^="title-"]');
                const slugInput = document.querySelector('input[id^="slug-"]');
                if (titleInput && slugInput) {
                    titleInput.addEventListener('keyup', () => {
                        slugInput.value = slugify(titleInput.value);
                    });
                }
            };

            const setupTomSelect = () => {
                if (document.getElementById('tags-select')) {
                    new TomSelect('#tags-select', {
                        plugins: ['remove_button'],
                        create: true,
                        persist: false,
                        placeholder: 'Nhập để tìm hoặc thêm tag mới...',
                    });
                }
                if (document.getElementById('related-posts-select')) {
                    new TomSelect('#related-posts-select', {
                        plugins: ['remove_button'],
                        create: false,
                        persist: false,
                        placeholder: 'Gõ để tìm kiếm bài viết...',
                    });
                }
            };

            const setupSeoThumbnailManager = () => {
                const seoInput = document.querySelector('input[id^="seo-thumbnail-input-"]');
                if (!seoInput) return;

                const locale = seoInput.id.split('-').pop();
                const removeButton = document.getElementById(`remove-seo-thumbnail-button-${locale}`);
                const removeInput = document.getElementById(`remove-seo-thumbnail-input-${locale}`);
                const previewImage = document.getElementById(`seo-thumbnail-preview-${locale}`);
                const existingContainer = document.getElementById(`existing-seo-thumbnail-container-${locale}`);
                const placeholder = document.getElementById(`seo-thumbnail-placeholder-${locale}`);
                const googleImageContainer = document.getElementById(
                    `google-preview-image-container-${locale}`);
                const googleImage = googleImageContainer ? googleImageContainer.querySelector('img') : null;
                const googlePlaceholder = document.getElementById(`google-preview-placeholder-${locale}`);

                seoInput.addEventListener('change', (event) => {
                    const file = event.target.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const imageUrl = e.target.result;
                        previewImage.src = imageUrl;
                        previewImage.classList.remove('hidden');
                        existingContainer.classList.add('hidden');
                        placeholder.classList.add('hidden');
                        removeButton.classList.remove('hidden');
                        removeInput.value = '0';
                        if (googleImage) {
                            googleImage.src = imageUrl;
                            googleImageContainer.classList.remove('hidden');
                            googlePlaceholder.classList.add('hidden');
                        }
                    };
                    reader.readAsDataURL(file);
                });

                removeButton.addEventListener('click', () => {
                    seoInput.value = null;
                    previewImage.src = '';
                    previewImage.classList.add('hidden');
                    existingContainer.classList.add('hidden');
                    placeholder.classList.remove('hidden');
                    removeButton.classList.add('hidden');
                    removeInput.value = '1';
                    if (googleImage) {
                        googleImage.src = '';
                        googleImageContainer.classList.add('hidden');
                        googlePlaceholder.classList.remove('hidden');
                    }
                });
            };

            const setupSeoPreviews = () => {
                const titleInput = document.querySelector('input[id^="seo_title-"]');
                if (!titleInput) return;

                const locale = titleInput.id.split('-')[1];
                const descInput = document.getElementById(`seo_description-${locale}`);
                const titleCount = document.getElementById(`title-count-${locale}`);
                const descCount = document.getElementById(`desc-count-${locale}`);
                const titleProgress = document.getElementById(`title-progress-${locale}`);
                const descProgress = document.getElementById(`desc-progress-${locale}`);
                const previewTitle = document.getElementById(`google-title-${locale}`);
                const previewDesc = document.getElementById(`google-description-${locale}`);

                const updateProgress = (input, countEl, progressEl, max) => {
                    const len = input.value.length;
                    const percent = Math.min((len / max) * 100, 100);
                    countEl.textContent = len;
                    progressEl.style.width = percent + '%';
                };

                titleInput.addEventListener('input', function() {
                    updateProgress(titleInput, titleCount, titleProgress, 70);
                    previewTitle.textContent = titleInput.value || 'Tiêu đề bài viết';
                });

                descInput.addEventListener('input', function() {
                    updateProgress(descInput, descCount, descProgress, 170);
                    previewDesc.textContent = descInput.value || 'Mô tả bài viết...';
                });

                updateProgress(titleInput, titleCount, titleProgress, 70);
                updateProgress(descInput, descCount, descProgress, 170);
            };

            const initTinyMCE = () => {
                tinymce.init({
                    selector: 'textarea[name*="[content]"], textarea[name*="[excerpt]"]',
                    height: 500,
                    menubar: false,
                    plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
                    toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image custom_image_upload | removeformat | code | fullscreen',
                    branding: false,
                    block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
                    setup: function(editor) {
                        editor.ui.registry.addButton('custom_image_upload', {
                            text: 'Tải ảnh lên',
                            icon: 'image',
                            tooltip: 'Tải ảnh từ máy tính',
                            onAction: function() {
                                const input = document.createElement('input');
                                input.setAttribute('type', 'file');
                                input.setAttribute('accept', 'image/*');

                                input.onchange = function() {
                                    const file = this.files[0];
                                    if (!file) return;


                                    const titleInput = document.getElementById(
                                        'title-vi') || document.querySelector(
                                        'input[id^="title-"]');
                                    const postTitle = titleInput ? titleInput
                                        .value : 'untitled-post';

                                    editor.setProgressState(true);
                                    const formData = new FormData();
                                    formData.append('image', file);


                                    formData.append('baseName', postTitle);

                                    const csrfToken = document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute(
                                        'content');

                                    // SỬA LẠI TÊN ROUTE CHO ĐÚNG
                                    fetch("{{ route('upload.image') }}", {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': csrfToken,
                                                'Accept': 'application/json'
                                            },
                                            body: formData
                                        })
                                        // CẬP NHẬT CÁCH XỬ LÝ RESPONSE ĐỂ BẮT LỖI
                                        .then(response => {
                                            if (!response
                                                .ok
                                            ) { // Kiểm tra nếu có lỗi (vd: 422, 500)
                                                return response.json().then(
                                                    err => {
                                                        throw err;
                                                    });
                                            }
                                            return response
                                                .json(); // Nếu thành công
                                        })
                                        .then(result => {
                                            editor.setProgressState(false);
                                            if (result.location) {
                                                editor.execCommand(
                                                    'mceInsertContent',
                                                    false,
                                                    `<img src="${result.location}" alt="${file.name}" style="max-width: 100%;">`
                                                );
                                            }
                                        })
                                        .catch(error => {
                                            editor.setProgressState(false);
                                            // Hiển thị lỗi cụ thể từ server
                                            editor.notificationManager.open({
                                                text: 'Lỗi khi upload: ' +
                                                    (error.message ||
                                                        'Lỗi không xác định.'
                                                    ),
                                                type: 'error'
                                            });
                                        });
                                };
                                input.click();
                            }
                        });

                        editor.on('change', function() {
                            tinymce.triggerSave();
                        });
                    }
                });
            };

            const setupPostInserter = () => {
                const insertButton = document.getElementById('insert-post-button');
                const modal = document.getElementById('post-selection-modal');
                const closeButton = document.getElementById('modal-close-button');
                const postList = document.getElementById('modal-post-list');
                const searchInput = document.getElementById('modal-post-search');

                if (!insertButton || !modal || !closeButton || !postList || !searchInput) return;

                const renderPostList = (posts) => {
                    postList.innerHTML = '';
                    if (posts.length === 0) {
                        postList.innerHTML =
                            '<li class="p-3 text-gray-500">Không tìm thấy bài viết nào.</li>';
                        return;
                    }
                    posts.forEach(post => {
                        const li = document.createElement('li');
                        li.className = 'p-3 hover:bg-gray-100 cursor-pointer';
                        li.textContent = post.title;
                        li.dataset.id = post.id;
                        postList.appendChild(li);
                    });
                };

                const closeModal = () => modal.classList.add('hidden');
                insertButton.addEventListener('click', () => {
                    renderPostList(allPostsForModal);
                    modal.classList.remove('hidden');
                });
                closeButton.addEventListener('click', closeModal);
                modal.addEventListener('click', (event) => {
                    if (event.target === modal) closeModal();
                });

                searchInput.addEventListener('keyup', () => {
                    const searchTerm = searchInput.value.toLowerCase();
                    const filteredPosts = allPostsForModal.filter(post =>
                        post.title.toLowerCase().includes(searchTerm)
                    );
                    renderPostList(filteredPosts);
                });

                postList.addEventListener('click', (event) => {
                    const targetLi = event.target.closest('li[data-id]');
                    if (!targetLi) return;

                    const postId = targetLi.dataset.id;
                    const shortcode = `<p>[baiviet id=${postId}]</p>`;
                    tinymce.activeEditor.execCommand('mceInsertContent', false, shortcode);
                    closeModal();
                });
            };
            const setupPreviewButton = () => {
                const previewButton = document.getElementById('preview-button');
                const form = document.getElementById('post-form');
                const previewUrl = "{{ route('admin.posts.preview', [], false) }}";

                if (!previewButton || !form) {
                    console.error('Preview button or post form not found!');
                    return;
                }

                previewButton.addEventListener('click', () => {
                    const originalAction = form.action;
                    const originalTarget = form.target;

                    const methodInput = form.querySelector('input[name="_method"]');

                    if (window.tinymce) {
                        tinymce.triggerSave();
                    }
                    if (methodInput) {
                        methodInput.disabled = true;
                    }

                    form.action = previewUrl;
                    form.target = '_blank';
                    form.submit();


                    setTimeout(() => {
                        form.action = originalAction;
                        form.target = originalTarget;
                        if (methodInput) {
                            methodInput.disabled = false;
                        }
                    }, 100);
                });
            };



            setupPreviewButton();

            setupTabs();
            setupThumbnailManager();
            setupSlugGenerator();
            setupTomSelect();
            setupSeoThumbnailManager();
            setupSeoPreviews();
            initTinyMCE();
            setupPostInserter();
        });
    </script>
@endpush
