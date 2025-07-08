<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unit;
use App\Traits\Paginatable;

class UnitController extends Controller
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
            $query = Unit::where('id', $id);

            if (!$isBuyer) {
                $query->where('user_id', $user->id);
            }

            $unit = $query->firstOrFail();
            return $this->apiResponse('Unit fetched successfully.', $unit);
        }

        $query = Unit::query();

        if (!$isBuyer) {
            $query->where('user_id', $user->id);
        }

        return $this->apiResponse('Units fetched successfully.', $this->paginateQuery($query->latest()));
    }

    public function store(Request $request)
    {
        if (auth()->user()->hasRole('buyer')) {
            return $this->apiResponse('Buyers are not allowed to add units.', null, 403);
        }

        $request->validate(['name' => 'required|string|max:255']);

        $unit = Unit::create([
            'name' => $request->name,
            'user_id' => auth()->id(),
        ]);

        return $this->apiResponse('Unit created successfully.', $unit, 201);
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return $this->apiResponse('Buyers are not allowed to update units.', null, 403);
        }

        $request->validate(['name' => 'required|string|max:255']);

        $unit = Unit::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $unit->name = $request->name;
        $unit->save();

        return $this->apiResponse('Unit updated successfully.', $unit);
    }

    public function destroy($id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return $this->apiResponse('Buyers are not allowed to delete units.', null, 403);
        }

        $unit = Unit::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $unit->delete();

        return $this->apiResponse('Unit deleted successfully.');
    }
}
