<?php

namespace App\Http\Controllers;

use App\Models\{StoreCategory};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\Paginatable;

class StoreCategoryController extends Controller
{
    use Paginatable;

    public function index(Request $request, $id = null)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->apiResponse('Unauthenticated.', null, 401);
        }

        $isBuyer = $user->hasRole('buyer');

        if ($id) {
            $query = StoreCategory::where('id', $id);

            if (!$isBuyer) {
                $query->where('user_id', $user->id);
            }

            $category = $query->firstOrFail();
            return $this->apiResponse('Category fetched successfully', $category);
        }

        $query = StoreCategory::query();

        if (!$isBuyer) {
            $query->where('user_id', $user->id);
        }

        return $this->apiResponse('Categories fetched successfully', $this->paginateQuery($query->latest()));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if (auth()->user()->hasRole('buyer')) {
            return $this->apiResponse('Buyers are not allowed to add categories.', null, 403);
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

        return $this->apiResponse('Category created successfully', $category, 201);
    }

    public function show($id)
    {
        $cat = StoreCategory::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return $this->apiResponse('Category fetched successfully', $cat);
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return $this->apiResponse('Buyers are not allowed to update categories.', null, 403);
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

        return $this->apiResponse('Category updated successfully', $cat);
    }

    public function destroy($id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return $this->apiResponse('Buyers are not allowed to delete categories.', null, 403);
        }

        $cat = StoreCategory::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($cat->image && \Storage::disk('public')->exists($cat->image)) {
            \Storage::disk('public')->delete($cat->image);
        }

        $cat->delete();

        return $this->apiResponse('Category deleted successfully');
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

        return $this->apiResponse('Category with stores fetched successfully', $category);
    }
}
