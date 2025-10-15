<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryType extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['code', 'name', 'description', 'is_active'];

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'type_id');
    }
}
