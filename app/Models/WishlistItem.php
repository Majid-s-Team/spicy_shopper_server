<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WishlistItem extends Model
{
    use HasFactory;

    protected $fillable = ['wishlist_folder_id', 'product_id','quantity'];

    public function folder() {
        return $this->belongsTo(WishlistFolder::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
