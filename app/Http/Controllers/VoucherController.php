<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\OrderVoucherUsage;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::all();
        return $this->apiResponse('Vouchers fetched successfully.', $vouchers);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:vouchers,code',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'expires_at' => 'nullable|date',
        ]);

        $voucher = Voucher::create($request->all());

        return $this->apiResponse('Voucher created successfully.', $voucher, 201);
    }

    public function usages($voucherId)
    {
        $usages = OrderVoucherUsage::with(['user', 'order'])
                    ->where('voucher_id', $voucherId)
                    ->get();

        return $this->apiResponse('Voucher usages fetched successfully.', $usages);
    }
}
