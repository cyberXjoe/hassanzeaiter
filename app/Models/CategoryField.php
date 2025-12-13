<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryField extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'handle',
        'type',
        'required',
        'meta',
        'external_id'
    ];

    protected $casts = [
        'meta' => 'array',
        'required' => 'boolean'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function options()
    {
        return $this->hasMany(CategoryFieldOption::class);
    }
}
