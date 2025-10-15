<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [

        'author_id',
        'thumbnail',
        'status',
        'is_featured',
        'published_at',
        'view_count',
        'last_indexed_at',
        'is_indexable',
    ];

    protected $casts = [
        'is_indexable' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'last_indexed_at' => 'datetime',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_post');
    }


    // Một bài viết thuộc về một tác giả (User)
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }





    // Một bài viết có nhiều bản dịch
    public function translations(): HasMany
    {
        return $this->hasMany(PostTranslation::class, 'post_id');
    }

    // Lấy bản dịch theo ngôn ngữ hiện tại
    public function currentTranslation(): HasOne
    {
        $locale = app()->getLocale();
        return $this->hasOne(PostTranslation::class, 'post_id')->where('locale_code', $locale);
    }

    public function relatedPosts(): BelongsToMany
    {
        return $this->belongsToMany(
            Post::class,
            'related_posts',
            'post_id',
            'related_post_id'
        );
    }
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }
}
