<?php

namespace App\Http\Controllers;

use App\Models\{Store, StoreCategory, Product, ProductCategory, Unit, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\Paginatable;
use Spatie\Permission\Traits\HasRoles;
use Paginator;
class StoreCategoryController extends Controller
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
            $query = StoreCategory::where('id', $id);

            if (!$isBuyer) {
                $query->where('user_id', $user->id);
            }

            $category = $query->firstOrFail();
            return response()->json($category);
        }

        $query = StoreCategory::query();

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
                'message' => 'Buyers are not allowed to add categories.'
            ], 403);
        }

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('store_categories', 'public');
        }

        $category = StoreCategory::create([
            'name' => $request->name,
            'image' => $imagePath,
            'user_id' => auth()->id(),
        ]);

        return response()->json($category, 201);
    }

    public function show($id)
    {
        $cat = StoreCategory::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        return response()->json($cat, 201);
    }


    public function update(Request $request, $id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return response()->json([
                'message' => 'Buyers are not allowed to update categories.'
            ], 403);
        }

        $request->validate([
            'name' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);


        $cat = StoreCategory::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($request->hasFile('image')) {
            if ($cat->image && \Storage::disk('public')->exists($cat->image)) {
                \Storage::disk('public')->delete($cat->image);
            }

            $cat->image = $request->file('image')->store('store_categories', 'public');
        }

        $cat->name = $request->name;
        $cat->save();

        return response()->json($cat);
    }
    public function destroy($id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return response()->json([
                'message' => 'Buyers are not allowed to delete categories.'
            ], 403);
        }

        $cat = StoreCategory::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($cat->image && \Storage::disk('public')->exists($cat->image)) {
            \Storage::disk('public')->delete($cat->image);
        }

        $cat->delete();

        return response()->json(['message' => 'Deleted']);
    }
    public function showWithStores($id)
    {
        $user = auth()->user();

        $query = StoreCategory::with([
            'stores' => function ($q) use ($user) {
                if (!$user->hasRole('buyer')) {
                    $q->where('user_id', $user->id);
                }
            }
        ])->where('id', $id);

        if (!$user->hasRole('buyer')) {
            $query->where('user_id', $user->id);
        }

        $category = $query->firstOrFail();

        return response()->json($category);
    }



}
