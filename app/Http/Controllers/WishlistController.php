<?php

namespace App\Http\Controllers;

use App\Models\WishlistFolder;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    // Create Folder
    public function createFolder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->apiResponse('Validation Error', $validator->errors(), 422);
        }

        $folder = WishlistFolder::create([
            'user_id' => Auth::id(),
            'name' => $request->name
        ]);

        return $this->apiResponse('Folder created successfully', $folder);
    }

    // Get all folders with items
    public function getFolders()
    {
        $folders = WishlistFolder::with('items.product')->where('user_id', Auth::id())->get();
        return $this->apiResponse('Folders fetched successfully', $folders);
    }

    // Update folder
    public function updateFolder(Request $request, $id)
    {
        $folder = WishlistFolder::where('user_id', Auth::id())->find($id);
        if (!$folder) {
            return $this->apiResponse('Folder not found', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->apiResponse('Validation Error', $validator->errors(), 422);
        }

        $folder->update(['name' => $request->name]);

        return $this->apiResponse('Folder updated successfully', $folder);
    }
    public function updateQuantity(Request $request, $folderId, $productId)
{
    $validator = Validator::make($request->all(), [
        'quantity' => 'required|integer|min:1'
    ]);

    if ($validator->fails()) {
        return $this->apiResponse('Validation Error', $validator->errors(), 422);
    }

    $folder = WishlistFolder::where('user_id', Auth::id())->find($folderId);
    if (!$folder) {
        return $this->apiResponse('Folder not found', null, 404);
    }

    $item = WishlistItem::where('wishlist_folder_id', $folder->id)
        ->where('product_id', $productId)
        ->first();

    if (!$item) {
        return $this->apiResponse('Product not found in folder', null, 404);
    }

    $item->quantity = $request->quantity;
    $item->save();

    return $this->apiResponse('Quantity updated successfully', $item);
}


    // Delete folder
    public function deleteFolder($id)
    {
        $folder = WishlistFolder::where('user_id', Auth::id())->find($id);
        if (!$folder) {
            return $this->apiResponse('Folder not found', null, 404);
        }

        $folder->delete();

        return $this->apiResponse('Folder deleted successfully');
    }

    // Add Product to folder
   // Add Product to folder
public function addProduct(Request $request, $folderId)
{
    $validator = Validator::make($request->all(), [
        'product_id' => 'required|exists:products,id',
        'quantity'   => 'nullable|integer|min:1'
    ]);

    if ($validator->fails()) {
        return $this->apiResponse('Validation Error', $validator->errors(), 422);
    }

    $folder = WishlistFolder::where('user_id', Auth::id())->find($folderId);
    if (!$folder) {
        return $this->apiResponse('Folder not found', null, 404);
    }

    $item = WishlistItem::where('wishlist_folder_id', $folder->id)
        ->where('product_id', $request->product_id)
        ->first();

    if ($item) {

        $item->quantity += $request->quantity ?? 1;
        $item->save();
    } else {
        $item = WishlistItem::create([
            'wishlist_folder_id' => $folder->id,
            'product_id'         => $request->product_id,
            'quantity'           => $request->quantity ?? 1,
        ]);
    }

    return $this->apiResponse('Product added/updated successfully', $item);
}


    // Remove product from folder
    public function removeProduct($folderId, $productId)
    {
        $folder = WishlistFolder::where('user_id', Auth::id())->find($folderId);
        if (!$folder) {
            return $this->apiResponse('Folder not found', null, 404);
        }

        $item = WishlistItem::where('wishlist_folder_id', $folderId)->where('product_id', $productId)->first();

        if (!$item) {
            return $this->apiResponse('Product not found in folder', null, 404);
        }

        $item->delete();

        return $this->apiResponse('Product removed successfully');
    }
}
