<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\Paginatable;

class StoreController extends Controller
{
    use Paginatable;

    public function index(Request $request, $id = null)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $isBuyer = $user->hasRole('buyer');

        if ($id) {
            $query = Store::with(['category', 'user'])->where('id', $id);

            if (!$isBuyer) {
                $query->where('user_id', $user->id);
            }

            $store = $query->firstOrFail();
            return response()->json($store);
        }

        $query = Store::with(['category', 'user']);

        if (!$isBuyer) {
            $query->where('user_id', $user->id);
        }

        return $this->paginateQuery($query->latest());
    }

    public function store(Request $request)
    {
        if (auth()->user()->hasRole('buyer')) {
            return response()->json(['message' => 'Buyers are not allowed to add stores.'], 403);
        }

        $request->validate([
            'store_category_id' => 'required|exists:store_categories,id',
            'name' => 'required',
            'description' => 'nullable',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('stores', 'public');
        }

        $store = Store::create([
            'user_id' => auth()->id(),
            'store_category_id' => $request->store_category_id,
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imagePath,
        ]);

        return response()->json($store, 201);
    }

    public function show($id)
    {
        $store = Store::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return $store;
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return response()->json(['message' => 'Buyers are not allowed to update stores.'], 403);
        }

        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'store_category_id' => 'required|exists:store_categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $store = Store::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        if ($request->hasFile('image')) {
            if ($store->image && Storage::disk('public')->exists($store->image)) {
                Storage::disk('public')->delete($store->image);
            }

            $store->image = $request->file('image')->store('stores', 'public');
        }

        $store->name = $request->name;
        $store->description = $request->description;
        $store->store_category_id = $request->store_category_id;
        $store->save();

        return response()->json($store);
    }

    public function destroy($id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return response()->json(['message' => 'Buyers are not allowed to delete stores.'], 403);
        }

        $store = Store::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        if ($store->image && Storage::disk('public')->exists($store->image)) {
            Storage::disk('public')->delete($store->image);
        }

        $store->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
