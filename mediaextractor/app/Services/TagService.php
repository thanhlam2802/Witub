<?php

namespace App\Services;

use App\Repositories\TagRepository;

class TagService
{
    protected TagRepository $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * Xử lý một mảng các tag từ request.
     * Tạo mới nếu tag là chuỗi, giữ nguyên nếu là ID.
     *
     * @param array $tags Mảng các ID (int) và tên tag mới (string).
     * @return array Mảng chứa ID của tất cả các tag.
     */
    public function processTags(array $tags): array
    {
        $tagIds = [];
        foreach ($tags as $tag) {
            if (is_numeric($tag)) {
                // Nếu là số, đây là ID của tag đã có
                $tagIds[] = (int) $tag;
            } else {
                // Nếu là chuỗi, đây là tag mới cần tạo
                $newTag = $this->tagRepository->findOrCreate($tag);
                $tagIds[] = $newTag->id;
            }
        }
        return array_unique($tagIds);
    }
}
