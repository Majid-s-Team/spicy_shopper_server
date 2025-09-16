<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Cart, Product, Order, OrderItem,OrderVoucherUsage,Voucher};
use Illuminate\Support\Facades\DB;

class CartOrderController extends Controller
{
    public function addMultipleToCart(Request $request)
{
    $request->validate([
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1'
    ]);

    $userId = auth()->id();
    $responses = [];

    foreach ($request->items as $item) {
        $product = Product::findOrFail($item['product_id']);

        if ($product->quantity < $item['quantity']) {
            $responses[] = [
                'product_id' => $product->id,
                'message' => 'Insufficient stock',
                'status' => 'failed',
                'cart' => null
            ];
            continue;
        }

        $cart = Cart::updateOrCreate(
            ['user_id' => $userId, 'product_id' => $product->id],
            ['quantity' => DB::raw("quantity + {$item['quantity']}")]
        );

        // Refresh to get latest data
        $cart->refresh();

        $responses[] = [
            'product_id' => $product->id,
            'message' => 'Added to cart',
            'status' => 'success',
            'cart' => [
                'id' => $cart->id,
                'user_id' => $cart->user_id,
                'product_id' => $cart->product_id,
                'quantity' => $cart->quantity,
                'created_at' => $cart->created_at,
                'updated_at' => $cart->updated_at,
            ]
        ];
    }
    
    return $this->apiResponse('Cart updated', $responses);
}

    public function updateCartItem(Request $request, $cartId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('id', $cartId)->where('user_id', auth()->id())->firstOrFail();

        if ($cart->product->quantity < $request->quantity) {
            return response()->json(['message' => 'Insufficient stock.'], 400);
        }

        $cart->update(['quantity' => $request->quantity]);

        // return response()->json(['message' => 'Cart updated.', 'cart' => $cart]);
        return $this->apiResponse('Cart updated.', $cart);

    }

    public function removeCartItem($cartId)
    {
        $cart = Cart::where('id', $cartId)->where('user_id', auth()->id())->firstOrFail();
        $cart->delete();

        // return response()->json(['message' => 'Item removed from cart.']);
        return $this->apiResponse('Item removed from cart.');

    }
    public function clearCart()
    {
        Cart::where('user_id', auth()->id())->delete();
        // return response()->json(['message' => 'All items removed from cart.']);
        return $this->apiResponse('All items removed from cart.');

    }
    public function getCart()
    {
        $cartItems = Cart::with('product')->where('user_id', auth()->id())->get();

        return $this->apiResponse('Cart fetched', [
            'cart' => $cartItems,
            'total_items' => $cartItems->count(),
            'total_price' => $cartItems->sum(fn($item) => $item->product->price * $item->quantity)
        ]);
    }



    public function checkout(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:cod,card,credit_debit',
            'voucher_code' => 'nullable|string'

        ]);

        $cartItems = Cart::with('product')->where('user_id', auth()->id())->get();
        if ($cartItems->isEmpty()) {
            // return response()->json(['message' => 'Cart is empty.'], 400);
                        return $this->apiResponse('Cart is empty.', null, 400);

        }

        DB::beginTransaction();
        try {
            $total = 0;
            foreach ($cartItems as $item) {
                if ($item->product->quantity < $item->quantity) {
                    throw new \Exception("Product {$item->product->name} out of stock.");
                }
                $total += $item->product->price * $item->quantity;
            }

            $discount = 0;
            if ($request->voucher_code) {
                $voucher = Voucher::where('code', $request->voucher_code)->first();
                if ($voucher && $voucher->isValid()) {
                    if ($voucher->discount_amount) {
                        $discount = $voucher->discount_amount;
                    } elseif ($voucher->discount_percent) {
                        $discount = ($total * $voucher->discount_percent) / 100;
                    }
                } else {
                    // return response()->json(['message' => 'Invalid or expired voucher.'], 400);
                                        return $this->apiResponse('Invalid or expired voucher.', null, 400);

                }
            }

            $finalAmount = max($total - $discount, 0);

            $order = Order::create([
                'user_id' => auth()->id(),
                'total_amount' => $finalAmount,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method == 'cod' ? 'pending' : 'paid'
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);

                $item->product->decrement('quantity', $item->quantity);
            }

            if ($request->voucher_code && isset($voucher)) {
                OrderVoucherUsage::create([
                    'order_id' => $order->id,
                    'voucher_id' => $voucher->id,
                    'user_id' => auth()->id(),
                    'discount_amount' => $discount
                ]);
            }


            Cart::where('user_id', auth()->id())->delete();

            DB::commit();
            // return response()->json([
            //     'message' => 'Order placed successfully',
            //     'order' => $order,
            //     'discount' => $discount,
            //     'total_before_discount' => $total
            // ]);
             return $this->apiResponse('Order placed successfully', [
                'order' => $order,
                'discount' => $discount,
                'total_before_discount' => $total
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // return response()->json(['error' => $e->getMessage()], 400);
            return $this->apiResponse($e->getMessage(), null, 400);

        }
    }
    public function getMyOrders()
    {
        $orders = Order::with(['items.product', 'voucherUsage.voucher'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return $this->apiResponse('Orders fetched successfully', $orders);
    }
}
