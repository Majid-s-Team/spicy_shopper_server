<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;
     protected $fillable = [
        'user_id',
        'store_category_id',
        'name',
        'image',
        'description',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function category()
    {
        return $this->belongsTo(StoreCategory::class, 'store_category_id');
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
