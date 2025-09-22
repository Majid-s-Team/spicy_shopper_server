<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'profile_image',
        'dob',
        'gender',
        'language',
        'location',
        'is_active',
        'is_blocked'
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function store()
    {
        return $this->hasOne(Store::class, 'user_id');
    }
public function wishlistFolders()
{
    return $this->hasMany(WishlistFolder::class, 'user_id');
}

public function wishlistItems()
{
    // user ke saare folders ke through items
    return $this->hasManyThrough(
        WishlistItem::class,
        WishlistFolder::class,
        'user_id',         // Foreign key on wishlist_folders table
        'wishlist_folder_id', // Foreign key on wishlist_items table
        'id',              // Local key on users table
        'id'               // Local key on wishlist_folders table
    );
}

public function favouriteProducts()
{
    return $this->belongsToMany(Product::class, 'wishlist_items', 'wishlist_folder_id', 'product_id')
        ->using(WishlistItem::class)
        ->withTimestamps();
}


}
