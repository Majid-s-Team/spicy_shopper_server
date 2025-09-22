<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'product_category_id',
        'unit_id',
        'name',
        'price',
        'quantity',
        'image',
        'discount',
        'description',
         'user_id' 
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
        public function wishlistItems()
{
    return $this->hasMany(WishlistItem::class, 'product_id');
}

}