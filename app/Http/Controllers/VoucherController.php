<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index()
    {
        return Voucher::all();
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
        return response()->json($voucher);
    }
    public function usages($voucherId)
{
    $usages = \App\Models\OrderVoucherUsage::with(['user', 'order'])
                ->where('voucher_id', $voucherId)
                ->get();

    return response()->json($usages);
}
}
