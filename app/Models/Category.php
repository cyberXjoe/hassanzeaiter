<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'external_id',
        'name',
        'slug',
        'raw'
    ];

    protected $casts = [
        'raw' => 'array'
    ];

    public function fields()
    {
        return $this->hasMany(CategoryField::class);
    }
}