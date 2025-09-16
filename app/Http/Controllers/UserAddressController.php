<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserAddressController extends Controller
{
    public function index()
    {
        $addresses = Auth::user()->addresses()->latest()->get();

        return $this->apiResponse('User addresses fetched successfully', $addresses);
    }

    // ✅ Store new address
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'address'     => 'required|string|max:500',
            'city'        => 'required|string|max:100',
            'state'       => 'nullable|string|max:100',
            'country'     => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse('Validation error', $validator->errors(), 422);
        }

        $address = UserAddress::create([
            'user_id'     => Auth::id(),
            'title'       => $request->title,
            'address'     => $request->address,
            'city'        => $request->city,
            'state'       => $request->state,
            'country'     => $request->country,
            'postal_code' => $request->postal_code,
        ]);

        return $this->apiResponse('Address added successfully', $address);
    }

    // ✅ Show single address
    public function show($id)
    {
        $address = UserAddress::where('user_id', Auth::id())->find($id);

        if (!$address) {
            return $this->apiResponse('Address not found', null, 404);
        }

        return $this->apiResponse('Address fetched successfully', $address);
    }

    // ✅ Update address
    public function update(Request $request, $id)
    {
        $address = UserAddress::where('user_id', Auth::id())->find($id);

        if (!$address) {
            return $this->apiResponse('Address not found', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'title'       => 'sometimes|required|string|max:255',
            'address'     => 'sometimes|required|string|max:500',
            'city'        => 'sometimes|required|string|max:100',
            'state'       => 'nullable|string|max:100',
            'country'     => 'sometimes|required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse('Validation error', $validator->errors(), 422);
        }

        $address->update($request->only([
            'title',
            'address',
            'city',
            'state',
            'country',
            'postal_code'
        ]));

        return $this->apiResponse('Address updated successfully', $address);
    }

    // ✅ Delete address
    public function destroy($id)
    {
        $address = UserAddress::where('user_id', Auth::id())->find($id);

        if (!$address) {
            return $this->apiResponse('Address not found', null, 404);
        }

        $address->delete();

        return $this->apiResponse('Address deleted successfully');
    }
}
