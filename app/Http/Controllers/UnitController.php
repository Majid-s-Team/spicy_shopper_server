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
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $isBuyer = $user->hasRole('buyer');

        if ($id) {
            $query = Unit::where('id', $id);

            if (!$isBuyer) {
                $query->where('user_id', $user->id);
            }

            $unit = $query->firstOrFail();
            return response()->json($unit);
        }

        $query = Unit::query();

        if (!$isBuyer) {
            $query->where('user_id', $user->id);
        }

        return $this->paginateQuery($query->latest());
    }

    public function store(Request $request)
    {
        if (auth()->user()->hasRole('buyer')) {
            return response()->json(['message' => 'Buyers are not allowed to add units.'], 403);
        }

        $request->validate(['name' => 'required|string|max:255']);

        $unit = Unit::create([
            'name' => $request->name,
            'user_id' => auth()->id(),
        ]);

        return response()->json($unit, 201);
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return response()->json(['message' => 'Buyers are not allowed to update units.'], 403);
        }

        $request->validate(['name' => 'required|string|max:255']);

        $unit = Unit::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $unit->name = $request->name;
        $unit->save();

        return response()->json($unit);
    }

    public function destroy($id)
    {
        if (auth()->user()->hasRole('buyer')) {
            return response()->json(['message' => 'Buyers are not allowed to delete units.'], 403);
        }

        $unit = Unit::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $unit->delete();

        return response()->json(['message' => 'Deleted']);
    }
}