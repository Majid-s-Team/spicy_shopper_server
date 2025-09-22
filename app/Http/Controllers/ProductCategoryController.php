<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\Paginatable;

class ProductCategoryController extends Controller
{
    use Paginatable;

    public function index(Request $request, $id = null)
    {
        // $user = auth()->user();

        // if (!$user) {
        //     return response()->json([
        //         'message' => 'Unauthenticated.'
        //     ], 401);
        // }

        // $isBuyer = $user->hasRole('buyer');

        $searchName = $request->query('keyword');

        if ($id) {
            $query = ProductCategory::where('id', $id);

            // if (!$isBuyer) {
            //     $query->where('user_id', $user->id);
            // }

            $category = $query->firstOrFail();
            return $this->apiResponse('Category fetched', $category);
        }


        $query = ProductCategory::query();

        // if (!$isBuyer) {
        //     $query->where('user_id', $user->id);
        // }

        if ($searchName) {
            $query->where('name', 'LIKE', '%' . $searchName . '%');
        }

        $paginated = $this->paginateQuery($query->latest());

        return $this->apiResponse('Categories fetched', $paginated);
    }


    // public function index(Request $request, $id = null)
    // {
    //     $user = auth()->user();

    //     if (!$user) {
    //         return response()->json([
    //             'message' => 'Unauthenticated.'
    //         ], 401);
    //     }

    //     $isBuyer = $user->hasRole('buyer');

    //     if ($id) {
    //         $query = ProductCategory::where('id', $id);

    //         if (!$isBuyer) {
    //             $query->where('user_id', $user->id);
    //         }

    //         $category = $query->firstOrFail();
    //         // return response()->json($category);
    //         return $this->apiResponse('Category fetched', $category);

    //     }

    //     $query = ProductCategory::query();

    //     if (!$isBuyer) {
    //         $query->where('user_id', $user->id);
    //     }
    //     $paginated = $this->paginateQuery($query->latest());
    //     return $this->apiResponse('Categories fetched', $paginated);

    //     // return $this->paginateQuery($query->latest());
    // }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if (auth()->user()->hasRole('buyer')) {
            // return response()->json([
            //     'message' => 'Buyers are not allowed to add product categories.'
            // ], 403);
            return $this->apiResponse('Buyers are not allowed to add product categories.', null, 403);

        }

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('product_categories', 'public');
        }

        $category = ProductCategory::create([
            'name' => $request->name,
            'image' => $imagePath,
            'user_id' => auth()->id(),
        ]);

        return $this->apiResponse('Category created successfully', $category, 201);
    }

public function show($id)
{
    $cat = ProductCategory::find($id);

    if (!$cat) {
        return $this->apiResponse('Category not found.', null, 404);
    }

    return $this->apiResponse('Category fetched', $cat);
}


    public function update(Request $request, $id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return $this->apiResponse('Buyers are not allowed to update product categories.', null, 403);
        }

        $request->validate([
            'name' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $cat = ProductCategory::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($request->hasFile('image')) {
            if ($cat->image && \Storage::disk('public')->exists($cat->image)) {
                \Storage::disk('public')->delete($cat->image);
            }

            $cat->image = $request->file('image')->store('product_categories', 'public');
        }

        $cat->name = $request->name;
        $cat->save();

        return $this->apiResponse('Category updated', $cat);
    }

    public function destroy($id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return response()->json([
                'message' => 'Buyers are not allowed to delete product categories.'
            ], 403);
        }

        $cat = ProductCategory::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($cat->image && \Storage::disk('public')->exists($cat->image)) {
            \Storage::disk('public')->delete($cat->image);
        }

        $cat->delete();

        return $this->apiResponse('Category deleted successfully');
    }

   public function showWithProducts($id)
{
    $category = ProductCategory::with([
        'products' => function ($q) {
            $q->with(['unit', 'seller']);
        },
        'seller'
    ])->where('id', $id)->first();

    if (!$category) {
        return $this->apiResponse('Category not found.', null, 404);
    }

    return $this->apiResponse('Category with products fetched', $category);
}


//   public function allCategoriesWithProducts(Request $request)
// {
//     $user = auth()->user();

//     $categoryName = $request->query('category_name');
//     $productName = $request->query('product_name');
//     $perPage = $request->get('per_page', 10); 

//     $query = ProductCategory::with('seller');

//     if (!$user->hasRole('buyer')) {
//         $query->where('user_id', $user->id);
//     }

//     if ($categoryName) {
//         $query->where('name', 'like', '%' . $categoryName . '%');
//     }

//     $categories = $query->latest()->get();

//     if ($categories->isEmpty()) {
//         return $this->apiResponse('No categories found.', []);
//     }

//     $categories->each(function ($category) use ($user, $productName, $perPage) {
//         $productQuery = $category->products()->with(['unit', 'seller']);

//         if (!$user->hasRole('buyer')) {
//             $productQuery->where('user_id', $user->id);
//         }

//         if ($productName) {
//             $productQuery->where('name', 'like', '%' . $productName . '%');
//         }

//         $category->setRelation('products', $productQuery->paginate($perPage));
//     });

//     return $this->apiResponse('All categories with products fetched', $categories);
// }

    // public function allCategoriesWithProducts(Request $request)
    // {
    //     $categoryName = $request->query('category_name');
    //     $productName = $request->query('product_name');
    //     $perPage = $request->get('per_page', 10); 

    //     $query = ProductCategory::with('seller');

    //     if ($categoryName) {
    //         $query->where('name', 'like', '%' . $categoryName . '%');
    //     }

    //     $categories = $query->latest()->get();

    //     if ($categories->isEmpty()) {
    //         return $this->apiResponse('No categories found.', []);
    //     }

    //     $categories->each(function ($category) use ($productName, $perPage) {
    //         $productQuery = $category->products()->with(['unit', 'seller']);

    //         if ($productName) {
    //             $productQuery->where('name', 'like', '%' . $productName . '%');
    //         }

    //         $category->setRelation('products', $productQuery->paginate($perPage));
    //     });

    //     return $this->apiResponse('All categories with products fetched', $categories);
    // }
    public function allCategoriesWithProducts(Request $request)
{
    try {
        $user = JWTAuth::parseToken()->authenticate();
    } catch (JWTException $e) {
        $user = null;
    }

    $categoryName = $request->query('category_name');
    $productName  = $request->query('product_name');
    $perPage      = $request->get('per_page', 10);

    $query = ProductCategory::with('seller');

    if ($categoryName) {
        $query->where('name', 'like', '%' . $categoryName . '%');
    }

    $categories = $query->latest()->get();

    if ($categories->isEmpty()) {
        return $this->apiResponse('No categories found.', []);
    }

    $categories->each(function ($category) use ($productName, $perPage, $user) {
        $productQuery = $category->products()->with(['unit', 'seller']);

        if ($user) {
            $productQuery->withCount(['wishlistItems as wishlist_count' => function ($q) use ($user) {
                $q->whereHas('folder', function ($sub) use ($user) {
                    $sub->where('user_id', $user->id);
                });
            }]);
        } else {
            $productQuery->withCount(['wishlistItems as wishlist_count' => function ($q) {
                $q->whereRaw('0 = 1');
            }]);
        }

        if ($productName) {
            $productQuery->where('name', 'like', '%' . $productName . '%');
        }

        $paginatedProducts = $productQuery->paginate($perPage);

        $paginatedProducts->getCollection()->transform(function ($product) {
            $product->is_favourite = $product->wishlist_count > 0;
            unset($product->wishlist_count);
            return $product;
        });

        $category->setRelation('products', $paginatedProducts);
    });

    return $this->apiResponse('All categories with products fetched', $categories);
}


}
