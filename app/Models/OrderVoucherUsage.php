<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderVoucherUsage extends Model
{
    protected $fillable = ['order_id', 'voucher_id', 'user_id', 'discount_amount'];

    public function order() { return $this->belongsTo(Order::class); }
    public function voucher() { return $this->belongsTo(Voucher::class); }
    public function user() { return $this->belongsTo(User::class); }
}
