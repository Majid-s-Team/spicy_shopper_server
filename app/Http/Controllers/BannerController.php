<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        return response()->json(Banner::latest()->get());
    }

    public function store(Request $request)
    {
        if (auth()->user()->hasRole('buyer')) {
            return response()->json(['message' => 'Buyers are not allowed to create banners.'], 403);
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

        return response()->json($banner, 201);
    }

    public function show($id)
    {
        return response()->json(Banner::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return response()->json(['message' => 'Buyers are not allowed to update banners.'], 403);
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

        return response()->json($banner);
    }

    public function destroy($id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return response()->json(['message' => 'Buyers are not allowed to delete banners.'], 403);
        }

        $banner = Banner::findOrFail($id);
        $banner->delete();

        return response()->json(['message' => 'Banner deleted']);
    }

    public function changeStatus(Request $request, $id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return response()->json(['message' => 'Buyers are not allowed to change status.'], 403);
        }

        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $banner = Banner::findOrFail($id);
        $banner->status = $request->status;
        $banner->save();

        return response()->json(['message' => 'Status updated', 'banner' => $banner]);
    }
}