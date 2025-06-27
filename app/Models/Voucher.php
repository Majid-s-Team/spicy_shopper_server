<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code', 'discount_amount', 'discount_percent', 'expires_at', 'is_active'
    ];

    public function isValid()
    {
        return $this->is_active && (!$this->expires_at || now()->lt($this->expires_at));
    }
}
