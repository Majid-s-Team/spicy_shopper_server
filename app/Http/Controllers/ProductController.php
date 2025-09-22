<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\Paginatable;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ProductController extends Controller
{
    use Paginatable;
    
   public function index(Request $request, $id = null)
{
    // $user = JWTAuth::parseToken()->authenticate(); 
     try {
        $user = JWTAuth::parseToken()->authenticate();
    } catch (JWTException $e) {
        $user = null;
    }
    $searchName = $request->query('keyword');

    if ($id) {
        $query = Product::with(['store', 'category', 'unit']);

        if ($user) {
            $query->withCount(['wishlistItems as wishlist_count' => function ($q) use ($user) {
                $q->whereHas('folder', function ($sub) use ($user) {
                    $sub->where('user_id', $user->id);
                });
            }]);
        } else {
            $query->withCount(['wishlistItems as wishlist_count' => function ($q) {
                $q->whereRaw('0 = 1');
            }]);
        }

        $product = $query->where('id', $id)->firstOrFail();
        $product->is_favourite = $product->wishlist_count > 0;
        unset($product->wishlist_count);

        return $this->apiResponse('Product fetched successfully', $product);
    }

    $query = Product::with(['store', 'category', 'unit']);

    if ($user) {
        $query->withCount(['wishlistItems as wishlist_count' => function ($q) use ($user) {
            $q->whereHas('folder', function ($sub) use ($user) {
                $sub->where('user_id', $user->id);
            });
        }]);
    } else {
        $query->withCount(['wishlistItems as wishlist_count' => function ($q) {
            $q->whereRaw('0 = 1'); 
        }]);
    }

    if ($searchName) {
        $query->where('name', 'LIKE', '%' . $searchName . '%');
    }

    $paginated = $this->paginateQuery($query->latest());

    $paginated->getCollection()->transform(function ($product) {
        $product->is_favourite = $product->wishlist_count > 0;
        unset($product->wishlist_count);
        return $product;
    });

    return $this->apiResponse('Products fetched successfully', $paginated);
}

    public function productsByStore(Request $request, $storeId)
{
    try {
        $user = JWTAuth::parseToken()->authenticate();
    } catch (JWTException $e) {
        $user = null;
    }

    $query = Product::with(['store', 'category', 'unit'])
        ->where('store_id', $storeId);

    if ($user) {
        $query->withCount(['wishlistItems as wishlist_count' => function ($q) use ($user) {
            $q->whereHas('folder', function ($sub) use ($user) {
                $sub->where('user_id', $user->id);
            });
        }]);
    } else {
        $query->withCount(['wishlistItems as wishlist_count' => function ($q) {
            $q->whereRaw('0 = 1');
        }]);
    }

    if ($request->has('keyword')) {
        $query->where('name', 'LIKE', '%' . $request->query('keyword') . '%');
    }

    $paginated = $this->paginateQuery($query->latest());

    $paginated->getCollection()->transform(function ($product) {
        $product->is_favourite = $product->wishlist_count > 0;
        unset($product->wishlist_count);
        return $product;
    });

    return $this->apiResponse('Products fetched successfully', $paginated);
}



    // public function index(Request $request, $id = null)
    // {
    //     $user = auth()->user();

    //     if (!$user) {
    //         // return response()->json(['message' => 'Unauthenticated.'], 401);
    //         return $this->apiResponse('Unauthenticated.', null, 401);

    //     }

    //     $isBuyer = $user->hasRole('buyer');

    //     if ($id) {
    //         $query = Product::with(['store', 'category', 'unit'])->where('id', $id);

    //         if (!$isBuyer) {
    //             $query->where('user_id', $user->id);
    //         }

    //         $product = $query->firstOrFail();
    //         // return response()->json($product);
    //         return $this->apiResponse('Product fetched successfully', $product);

    //     }

    //     $query = Product::with(['store', 'category', 'unit']);

    //     if (!$isBuyer) {
    //         $query->where('user_id', $user->id);
    //     }
    //     $paginated = $this->paginateQuery($query->latest());
    //     return $this->apiResponse('Products fetched successfully', $paginated);

    //     // return $this->paginateQuery($query->latest());
    // }

    public function store(Request $request)
    {
        if (auth()->user()->hasRole('buyer')) {
            // return response()->json(['message' => 'Buyers are not allowed to add products.'], 403);
            return $this->apiResponse('Buyers are not allowed to add products.', null, 403);

        }

        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'product_category_id' => 'required|exists:product_categories,id',
            'unit_id' => 'required|exists:units,id',
            'name' => 'required',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'discount' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'description' => 'nullable|string',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'store_id' => $request->store_id,
            'product_category_id' => $request->product_category_id,
            'unit_id' => $request->unit_id,
            'name' => $request->name,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'discount' => $request->discount ?? 0,
            'description' => $request->description,
            'image' => $imagePath,
            'user_id' => auth()->id(),
        ]);

        // return response()->json($product, 201);
        return $this->apiResponse('Product created successfully', $product, 201);

    }

    public function show($id)
    {
        $product = Product::where('id', $id)
            ->where('user_id', auth()->id())
            ->with(['store', 'category', 'unit'])
            ->firstOrFail();

        // return response()->json($product);
        return $this->apiResponse('Product details fetched', $product);

    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return $this->apiResponse('Buyers are not allowed to update products.', null, 403);

        }

        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'discount' => 'nullable|integer',
            'description' => 'nullable|string',
            'product_category_id' => 'required|exists:product_categories,id',
            'unit_id' => 'required|exists:units,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $product = Product::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($request->hasFile('image')) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $product->image = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'name' => $request->name,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'discount' => $request->discount,
            'description' => $request->description,
            'product_category_id' => $request->product_category_id,
            'unit_id' => $request->unit_id,
        ]);

        return $this->apiResponse('Product updated successfully', $product);
    }

    public function destroy($id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return $this->apiResponse('Buyers are not allowed to delete products.', null, 403);
        }

        $product = Product::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        // return response()->json(['message' => 'Product deleted']);
        return $this->apiResponse('Product deleted successfully');

    }
}
