<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'image', 'user_id'];

    public function products()
    {
        return $this->hasMany(Product::class,'product_category_id');
    }


    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
}
