<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'price',
        'raw'
    ];

    protected $casts = [
        'raw' => 'array'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function fieldValues()
    {
        return $this->hasMany(AdFieldValue::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
