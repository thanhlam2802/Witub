<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_id',
        'parent_id',
        'thumbnail',
        'icon',
        'sort_order',
        'is_active',
        'last_indexed_at',
    ];
    protected $casts = [
        'last_indexed_at' => 'datetime',
    ];
    public function posts(): BelongsToMany
    {

        return $this->belongsToMany(Post::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(CategoryType::class, 'type_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function recursiveChildren(): HasMany
    {
        return $this->children()->with('recursiveChildren', 'translations', 'type');
    }

    public function currentTranslation(): HasOne
    {
        $locale = app()->getLocale();
        return $this->hasOne(CategoryTranslation::class, 'category_id')->where('locale_code', $locale);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class, 'category_id');
    }


    public function translation($locale = null)
    {
        if (is_null($locale)) {
            $locale = app()->getLocale();
        }
        return $this->translations()->where('locale_code', $locale)->first();
    }
}
