<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostTranslation extends Model
{
    use HasFactory;

    // Bảng này không cần cột created_at và updated_at
    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'locale_code',
        'title',
        'slug',
        'excerpt',
        'content',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'seo_canonical_url',
        'seo_thumbnail',
    ];

    /**
     * Lấy bài viết mà bản dịch này thuộc về.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
