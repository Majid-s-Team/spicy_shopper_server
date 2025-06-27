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
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $isBuyer = $user->hasRole('buyer');

        if ($id) {
            $query = ProductCategory::where('id', $id);

            if (!$isBuyer) {
                $query->where('user_id', $user->id);
            }

            $category = $query->firstOrFail();
            return response()->json($category);
        }

        $query = ProductCategory::query();

        if (!$isBuyer) {
            $query->where('user_id', $user->id);
        }

        return $this->paginateQuery($query->latest());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if (auth()->user()->hasRole('buyer')) {
            return response()->json([
                'message' => 'Buyers are not allowed to add product categories.'
            ], 403);
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

        return response()->json($category, 201);
    }

    public function show($id)
    {
        $cat = ProductCategory::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return $cat;
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return response()->json([
                'message' => 'Buyers are not allowed to update product categories.'
            ], 403);
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

        return response()->json($cat);
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

        return response()->json(['message' => 'Deleted']);
    }

public function showWithProducts($id)
{
    $user = auth()->user();

    $query = ProductCategory::with([
        'products' => function ($q) use ($user) {
            if (!$user->hasRole('buyer')) {
                $q->where('user_id', $user->id);
            }
            $q->with(['unit', 'seller']); 
        },
        'seller' 
    ])->where('id', $id);

    if (!$user->hasRole('buyer')) {
        $query->where('user_id', $user->id);
    }

    $category = $query->firstOrFail();

    return response()->json($category);
}

}
