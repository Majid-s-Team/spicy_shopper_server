<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::latest()->get();
        return $this->apiResponse('Banners fetched successfully.', $banners);
    }

    public function store(Request $request)
    {
        if (auth()->user()->hasRole('buyer')) {
            return $this->apiResponse('Buyers are not allowed to create banners.', null, 403);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('banners', 'public');
        }

        $banner = Banner::create([
            'title' => $request->title,
            'image' => $imagePath,
        ]);

        return $this->apiResponse('Banner created successfully.', $banner, 201);
    }

    public function show($id)
    {
        $banner = Banner::findOrFail($id);
        return $this->apiResponse('Banner retrieved successfully.', $banner);
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return $this->apiResponse('Buyers are not allowed to update banners.', null, 403);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $banner = Banner::findOrFail($id);

        if ($request->hasFile('image')) {
            if ($banner->image && Storage::disk('public')->exists($banner->image)) {
                Storage::disk('public')->delete($banner->image);
            }

            $banner->image = $request->file('image')->store('banners', 'public');
        }

        $banner->title = $request->title;
        $banner->save();

        return $this->apiResponse('Banner updated successfully.', $banner);
    }

    public function destroy($id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return $this->apiResponse('Buyers are not allowed to delete banners.', null, 403);
        }

        $banner = Banner::findOrFail($id);

        if ($banner->image && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();

        return $this->apiResponse('Banner deleted successfully.');
    }

    public function changeStatus(Request $request, $id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return $this->apiResponse('Buyers are not allowed to change status.', null, 403);
        }

        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $banner = Banner::findOrFail($id);
        $banner->status = $request->status;
        $banner->save();

        return $this->apiResponse('Status updated successfully.', $banner);
    }
}
