<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TagTranslation extends Model
{
    use HasFactory;


    public $timestamps = false;

    protected $fillable = [
        'tag_id',
        'locale_code',
        'name',
        'slug',
    ];


    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
