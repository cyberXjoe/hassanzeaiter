<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryFieldOption extends Model
{
    protected $fillable = [
        'category_field_id',
        'label',
        'value',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array'
    ];

    public function field()
    {
        return $this->belongsTo(CategoryField::class, 'category_field_id');
    }
}
