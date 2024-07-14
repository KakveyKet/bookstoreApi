<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Fillable fields for mass assignment
    protected $fillable = [
        'name', 'category_id', 'price', 'image', 'code'
    ];


    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return null;
    }
}
