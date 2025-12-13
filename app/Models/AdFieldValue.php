<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdFieldValue extends Model
{
    protected $fillable = [
        'ad_id',
        'category_field_id',
        'value',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array'
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function categoryField()
    {
        return $this->belongsTo(CategoryField::class, 'category_field_id');
    }
}
