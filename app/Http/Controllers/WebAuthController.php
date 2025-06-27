<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class WebAuthController extends Controller
{
    public function register(Request $request)
    {
        if (!in_array($request->role, ['seller', 'superadmin'])) {
            return response()->json(['error' => 'Only seller or superadmin roles allowed for web'], 403);
        }

      $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'password'      => Hash::make($request->password),
            'dob'           => $request->dob,
            'gender'        => $request->gender,
            'language'      => $request->language ?? 'en',
            'location'      => $request->location,
            'profile_image' => $request->profile_image ?? null,
        ]);

        UserAddress::create([
            'user_id'      => $user->id,
            'title'        => $request->address_title ?? 'Home',
            'address'      => $request->address,
            'city'         => $request->city,
            'state'        => $request->state,
            'country'      => $request->country,
            'postal_code'  => $request->postal_code,
        ]);

        $user->assignRole($request->role ?? 'buyer');
        \Log::info("Assigned roles: ", $user->getRoleNames()->toArray());

        $token = JWTAuth::fromUser($user);
        
        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid Credentials'], 401);
        }

        $user = auth()->user();

        if (!$user->hasAnyRole(['seller', 'superadmin'])) {
            return response()->json(['error' => 'Only seller/superadmin can login here'], 403);
        }

        return response()->json(['token' => $token, 'user' => $user]);
    }
}
