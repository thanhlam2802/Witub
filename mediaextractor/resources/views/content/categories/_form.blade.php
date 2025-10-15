@push('scripts')
    {{-- Script đơn giản để quản lý tabs --}}
    <script>
        function setupTabs() {
            document.querySelectorAll('[data-tab-toggle]').forEach(button => {
                button.addEventListener('click', () => {
                    const targetTab = document.querySelector(button.dataset.tabToggle);
                    document.querySelectorAll('[role="tabpanel"]').forEach(tabContent => tabContent
                        .classList.add('hidden'));
                    document.querySelectorAll('[role="tab"]').forEach(tab => {
                        tab.setAttribute('aria-selected', 'false');
                        tab.classList.remove('text-blue-600', 'border-blue-600');
                        tab.classList.add('hover:text-gray-600', 'hover:border-gray-300');
                    });
                    targetTab.classList.remove('hidden');
                    button.setAttribute('aria-selected', 'true');
                    button.classList.add('text-blue-600', 'border-blue-600');
                });
            });
        }
        document.addEventListener('DOMContentLoaded', setupTabs);
    </script>
    <script>
        function setupThumbnailManager() {
            const thumbnailInput = document.getElementById('thumbnail-input');
            const thumbnailPreview = document.getElementById('thumbnail-preview');
            const existingThumbnailContainer = document.getElementById('existing-thumbnail-container');
            const removeThumbnailButton = document.getElementById('remove-thumbnail-button');
            const removeThumbnailInput = document.getElementById('remove-thumbnail-input');


            if (existingThumbnailContainer && !existingThumbnailContainer.classList.contains('hidden')) {
                removeThumbnailButton.classList.remove('hidden');
            }

            thumbnailInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        thumbnailPreview.src = e.target.result;
                        thumbnailPreview.classList.remove('hidden');
                        removeThumbnailButton.classList.remove('hidden');
                        if (existingThumbnailContainer) {
                            existingThumbnailContainer.classList.add('hidden');
                        }
                        removeThumbnailInput.value = '0';
                    }
                    reader.readAsDataURL(file);
                }
            });


            removeThumbnailButton.addEventListener('click', function() {

                thumbnailInput.value = null;

                thumbnailPreview.classList.add('hidden');
                thumbnailPreview.src = '';
                removeThumbnailButton.classList.add('hidden');


                if (existingThumbnailContainer) {
                    existingThumbnailContainer.classList.add('hidden');
                    removeThumbnailInput.value = '1';
                }
            });
        }
        document.addEventListener('DOMContentLoaded', setupThumbnailManager);
    </script>
@endpush

<form action="{{ isset($category) ? route('categories.update', $category->id) : route('categories.store') }}"
    method="POST" enctype="multipart/form-data" class="p-4">
    @csrf
    @if ($errors->any())
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            <span class="font-medium">Có lỗi xảy ra, vui lòng kiểm tra lại:</span>
            <ul class="mt-1.5 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (isset($category))
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div
                class="p-4 space-y-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div>
                    <label for="parent_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Parent
                        Category</label>
                    <select id="parent_id" name="category[parent_id]"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600">
                        <option value="">— None —</option>
                        @foreach ($parentCategories as $categoryItem)
                            @include('content.categories._category-option', [
                                'categoryItem' => $categoryItem,
                                'level' => 0,
                                'category' => $category ?? null,
                            ])
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="type_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Category
                        Type</label>
                    <select id="type_id" name="category[type_id]"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600"
                        required>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}" @selected(old('category.type_id', $category->type_id ?? ($selectedTypeId ?? '')) == $type->id)>
                                {{ $type->name }}
                            </option>
                        @endforeach

                    </select>
                </div>
                <div>
                    <label for="sort_order" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Sort
                        Order</label>
                    <input type="number" name="category[sort_order]" id="sort_order"
                        value="{{ old('category.sort_order', $category->sort_order ?? 0) }}"
                        class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status</label>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="category[is_active]" value="0">
                        <input type="checkbox" name="category[is_active]" value="1" class="sr-only peer"
                            @checked(old('category.is_active', $category->is_active ?? true))>
                        <div
                            class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 dark:bg-gray-700
                   peer-checked:after:translate-x-full peer-checked:after:border-white
                   after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border
                   after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600
                   peer-checked:bg-green-600">
                        </div>
                        <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">Active</span>
                    </label>
                </div>


                <div>
                    <label for="thumbnail-input"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Thumbnail</label>

                    <div class="relative w-32 h-32">
                        {{-- Vùng hiển thị ảnh --}}
                        <div id="existing-thumbnail-container"
                            class="{{ isset($category) && $category->thumbnail ? '' : 'hidden' }}">
                            @if (isset($category) && $category->thumbnail)
                                <img src="{{ asset('storage/' . $category->thumbnail) }}" alt="Current Thumbnail"
                                    class="h-32 w-32 object-cover rounded-lg">
                            @endif
                        </div>
                        <img src="" alt="New Thumbnail Preview" id="thumbnail-preview"
                            class="h-32 w-32 object-cover rounded-lg hidden">

                        {{-- Nút xóa "x" --}}
                        <button type="button" id="remove-thumbnail-button"
                            class="absolute top-1 right-1 bg-white rounded-full p-1 leading-none text-gray-700 hover:bg-gray-200 hidden">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>

                    {{-- Input ẩn để gửi tín hiệu xóa ảnh cũ --}}
                    <input type="hidden" name="options[remove_thumbnail]" id="remove-thumbnail-input" value="0">

                    {{-- Input để tải file --}}
                    <input type="file" name="category[thumbnail]" id="thumbnail-input"
                        class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5"
                        accept="image/*">

                    {{-- Checkbox chuyển đổi sang WebP --}}
                    <div class="flex items-center mt-2">
                        <input id="convert_to_webp" name="options[convert_to_webp]" type="checkbox" value="1"
                            class="w-4 h-4 border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-primary-300 dark:focus:ring-primary-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600">
                        <label for="convert_to_webp" class="ml-2 text-sm  text-gray-700 dark:text-gray-300">Chuyển đổi
                            sang định dạng
                            .webp</label>
                    </div>
                </div>
                <div>
                    <label for="icon" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Icon (SVG
                        or Class)</label>
                    <textarea name="category[icon]" id="icon" rows="4"
                        class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600"
                        placeholder="<svg ...> or i-hugeicons-home">{{ old('category.icon', $category->icon ?? '') }}</textarea>
                    @error('category.icon')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="myTab" role="tablist">
                        @foreach ($locales as $locale)
                            <li class="mr-2" role="presentation">
                                <button
                                    class="inline-block p-4 border-b-2 rounded-t-lg {{ $loop->first ? 'text-blue-600 border-blue-600' : 'hover:text-gray-600 hover:border-gray-300' }}"
                                    data-tab-toggle="#content-{{ $locale->locale_code }}" type="button" role="tab"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ $locale->language_name }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div id="myTabContent" class="p-4 pt-0">
                    @foreach ($locales as $locale)
                        @php
                            $translation = isset($category)
                                ? $category->translations->firstWhere('locale_code', $locale->locale_code)
                                : null;
                        @endphp
                        <div class="space-y-4 {{ $loop->first ? '' : 'hidden' }}"
                            id="content-{{ $locale->locale_code }}" role="tabpanel">
                            <h3 class="text-lg font-semibold dark:text-white">Content
                                ({{ strtoupper($locale->locale_code) }})
                            </h3>
                            <div>
                                <label for="name_{{ $locale->locale_code }}"
                                    class="block mb-2 text-sm font-medium">Name</label>
                                <input type="text" name="translations[{{ $locale->locale_code }}][name]"
                                    value="{{ old('translations.' . $locale->locale_code . '.name', $translation->name ?? '') }}"
                                    id="name_{{ $locale->locale_code }}"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5"
                                    required>
                            </div>
                            <div>
                                <label for="slug_{{ $locale->locale_code }}"
                                    class="block mb-2 text-sm font-medium">Slug</label>
                                <input type="text" name="translations[{{ $locale->locale_code }}][slug]"
                                    value="{{ old('translations.' . $locale->locale_code . '.slug', $translation->slug ?? '') }}"
                                    id="slug_{{ $locale->locale_code }}"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5">
                            </div>
                            <div>
                                <label for="description_{{ $locale->locale_code }}"
                                    class="block mb-2 text-sm font-medium">Description</label>
                                <textarea name="translations[{{ $locale->locale_code }}][description]" rows="4"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5">{{ old('translations.' . $locale->locale_code . '.description', $translation->description ?? '') }}</textarea>
                            </div>

                            <hr class="dark:border-gray-600">

                            <h3 class="text-lg font-semibold dark:text-white">SEO
                                ({{ strtoupper($locale->locale_code) }})</h3>
                            <div>
                                <label for="seo_title_{{ $locale->locale_code }}"
                                    class="block mb-2 text-sm font-medium">SEO Title</label>
                                <input type="text" name="translations[{{ $locale->locale_code }}][seo_title]"
                                    value="{{ old('translations.' . $locale->locale_code . '.seo_title', $translation->seo_title ?? '') }}"
                                    id="seo_title_{{ $locale->locale_code }}"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5">
                            </div>
                            <div>
                                <label for="seo_description_{{ $locale->locale_code }}"
                                    class="block mb-2 text-sm font-medium">SEO Description</label>
                                <textarea name="translations[{{ $locale->locale_code }}][seo_description]" rows="3"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5">{{ old('translations.' . $locale->locale_code . '.seo_description', $translation->seo_description ?? '') }}</textarea>
                            </div>

                            {{-- ADDED START: SEO Keywords & Canonical URL --}}
                            <div>
                                <label for="seo_keywords_{{ $locale->locale_code }}"
                                    class="block mb-2 text-sm font-medium">SEO Keywords</label>
                                <input type="text" name="translations[{{ $locale->locale_code }}][seo_keywords]"
                                    value="{{ old('translations.' . $locale->locale_code . '.seo_keywords', $translation->seo_keywords ?? '') }}"
                                    id="seo_keywords_{{ $locale->locale_code }}"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5"
                                    placeholder="keyword 1, keyword 2, keyword 3">
                            </div>
                            <div>
                                <label for="seo_canonical_url_{{ $locale->locale_code }}"
                                    class="block mb-2 text-sm font-medium">Canonical URL</label>
                                <input type="url"
                                    name="translations[{{ $locale->locale_code }}][seo_canonical_url]"
                                    value="{{ old('translations.' . $locale->locale_code . '.seo_canonical_url', $translation->seo_canonical_url ?? '') }}"
                                    id="seo_canonical_url_{{ $locale->locale_code }}"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5"
                                    placeholder="https://example.com/canonical-url">
                            </div>


                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="mt-6">
        <button type="submit"
            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
            {{ isset($category) ? 'Update Category' : 'Save Category' }}
        </button>
    </div>
</form>
