<?php
namespace App\Http\Controllers;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\PasswordOtp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
class AppAuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        if ($request->role !== 'buyer') {
            return response()->json(['error' => 'Only buyer role is allowed in mobile app.'], 403);
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

        // return response()->json(['token' => $token, 'data' => $user]);
        return $this->apiResponse('Registration successful', [
    'token' => $token,
    'user' => $user
], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid Credentials'], 401);
        }

        $user = auth()->user();

        if (!$user->hasRole('buyer')) {
            return response()->json(['error' => 'Only buyer login allowed here.'], 403);
        }

        // return response()->json(['token' => $token, 'user' => $user]);
        return $this->apiResponse('Login successful', [
    'token' => $token,
    'user' => $user
]);
    }

        public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        $contact =  $request->email ?? $request->phone;

        if (!$contact) {
            return response()->json(['error' => 'Email or phone is required.'], 422);
        }

        $otp = rand(100000, 999999);

        PasswordOtp::updateOrCreate(
            ['email' => $request->email, 'phone' => $request->phone],
            ['otp' => $otp, 'expires_at' => Carbon::now()->addMinutes(10), 'is_verified' => false]
        );

        // You can use SMS or Mail based on the input
        if ($request->email) {
            Mail::raw("Your OTP is: $otp", function ($message) use ($request) {
                $message->to($request->email)->subject('Password Reset OTP');
            });
        }

        // return response()->json(['message' => 'OTP sent successfully.']);
        return $this->apiResponse('OTP sent successfully');

    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'otp' => 'required|string',
        ]);

        $otpRecord = PasswordOtp::where(function ($query) use ($request) {
            if ($request->email) {
                $query->where('email', $request->email);
            } else {
                $query->where('phone', $request->phone);
            }
        })->where('otp', $request->otp)->first();

        if (!$otpRecord || $otpRecord->expires_at < now()) {
            return response()->json(['error' => 'Invalid or expired OTP'], 422);
        }

        $otpRecord->update(['is_verified' => true]);

        // return response()->json(['message' => 'OTP verified successfully.']);
        return $this->apiResponse('OTP verified successfully');

    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'otp' => 'required|string',
            'password' => 'required|confirmed|min:6',
        ]);

        $otpRecord = PasswordOtp::where(function ($query) use ($request) {
            if ($request->email) {
                $query->where('email', $request->email);
            } else {
                $query->where('phone', $request->phone);
            }
        })->where('otp', $request->otp)
        ->where('is_verified', true)
        ->first();

        if (!$otpRecord) {
            return response()->json(['error' => 'OTP verification required'], 422);
        }

        $user = User::where('email', $request->email)
                    ->orWhere('phone', $request->phone)
                    ->first();

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        $user->update(['password' => Hash::make($request->password)]);

        // Clean up
        $otpRecord->delete();

        // return response()->json(['message' => 'Password reset successful.']);
        return $this->apiResponse('Password reset successful');

    }

    public function getProfile()
{
    $user = auth()->user();
    return $this->apiResponse('User profile fetched successfully', $user);
}

public function updateProfile(Request $request)
{
    $user = auth()->user();

    $request->validate([
        'name'     => 'sometimes|string|max:255',
        'phone'    => 'sometimes|string|max:20',
        'dob'      => 'nullable|date',
        'gender'   => 'nullable|in:male,female,other',
        'language' => 'nullable|string',
        'location' => 'nullable|string',
        'profile_image'    => 'nullable|string',
    ]);

    $user->update($request->only(['name', 'phone', 'dob', 'gender', 'language', 'location','profile_image']));

    return $this->apiResponse('Profile updated successfully', $user);
}

public function uploadProfileImage(Request $request)
{
    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
    ]);

    $file = $request->file('image');
    $filename = 'profile_' . time() . '.' . $file->getClientOriginalExtension();
    $path = $file->storeAs('public/profile_images', $filename);

    $url = asset(str_replace('public/', 'storage/', $path));

    return $this->apiResponse('Image uploaded successfully', ['url' => $url]);
}


}
