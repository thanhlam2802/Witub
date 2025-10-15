<?php

namespace App\Repositories;

use App\Models\Tag;
use App\Models\TagTranslation;
use Illuminate\Support\Str;

class TagRepository extends BaseRepository
{
    public function __construct(Tag $model)
    {
        parent::__construct($model);
    }

    /**
     * Tìm một tag bằng tên, nếu không có thì tạo mới.
     *
     * @param string $tagName Tên của tag.
     * @param string $locale Ngôn ngữ của tag.
     * @return Tag
     */
    public function findOrCreate(string $tagName, string $locale = 'vi'): Tag
    {
        // Tìm bản dịch của tag trước
        $translation = TagTranslation::where('name', $tagName)
            ->where('locale_code', $locale)
            ->first();

        // Nếu đã có, trả về tag cha
        if ($translation) {
            return $translation->tag;
        }

        // Nếu chưa có, tạo mới
        $tag = $this->model->create([]);
        $tag->translations()->create([
            'locale_code' => $locale,
            'name' => $tagName,
            'slug' => Str::slug($tagName),
        ]);

        return $tag;
    }
}
