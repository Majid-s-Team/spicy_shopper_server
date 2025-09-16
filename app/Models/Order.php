<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['user_id', 'total_amount', 'payment_method', 'payment_status','status','address_id','scheduled_date','scheduled_time'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

public function voucherUsage()
{
    return $this->hasOne(OrderVoucherUsage::class);
}
public function address() {
        return $this->belongsTo(UserAddress::class, 'address_id');
    }
}
