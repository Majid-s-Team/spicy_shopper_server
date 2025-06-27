<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreCategory extends Model
{
    use HasFactory;
    protected $fillable = ['name','image','user_id'];

    public function stores()
    {
        return $this->hasMany(Store::class);
    }


    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
