@csrf
<div class="space-y-4">
    <div>
        <label for="locale_code" class="block mb-2 text-sm font-medium">Mã Ngôn ngữ (e.g., vi, en)</label>
        <input type="text" id="locale_code" name="locale_code"
            value="{{ old('locale_code', $locale->locale_code ?? '') }}"
            class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5" required
            {{ isset($locale) ? 'disabled' : '' }}>
        @error('locale_code')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label for="language_name" class="block mb-2 text-sm font-medium">Tên Ngôn ngữ (e.g., Tiếng Việt)</label>
        <input type="text" id="language_name" name="language_name"
            value="{{ old('language_name', $locale->language_name ?? '') }}"
            class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5" required>
        @error('language_name')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div class="flex items-center">
        <input type="hidden" name="is_default" value="0">
        <input type="checkbox" name="is_default" value="1" id="is_default"
            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded" @checked(old('is_default', $locale->is_default ?? false))>
        <label for="is_default" class="ml-2 text-sm font-medium">Đặt làm mặc định?</label>
    </div>
    <div class="flex items-center">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" id="is_active"
            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded" @checked(old('is_active', $locale->is_active ?? true))>
        <label for="is_active" class="ml-2 text-sm font-medium">Kích hoạt?</label>
    </div>
</div>
<div class="mt-6">
    <button type="submit"
        class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
        {{ isset($locale) ? 'Cập nhật' : 'Lưu' }}
    </button>
    <a href="{{ route('locales.index') }}" class="ml-2 text-gray-600">Hủy bỏ</a>
</div>
