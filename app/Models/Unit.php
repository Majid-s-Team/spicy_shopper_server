<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'user_id'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}