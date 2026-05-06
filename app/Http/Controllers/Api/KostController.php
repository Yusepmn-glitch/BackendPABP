<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kost;
use App\Models\Room;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KostController extends Controller
{
    public function index(Request $request)
    {
        $query = Kost::with(['owner', 'rooms.photos', 'facilities']);
        if ($request->city) $query->where('city', $request->city);
        if ($request->type) $query->where('type', $request->type);
        if ($request->min_price) $query->whereHas('rooms', fn($q) => $q->where('price', '>=', $request->min_price));
        if ($request->max_price) $query->whereHas('rooms', fn($q) => $q->where('price', '<=', $request->max_price));
        // GPS filter
        if ($request->latitude && $request->longitude && $request->radius) {
            $lat = $request->latitude;
            $lng = $request->longitude;
            $radius = $request->radius;
            $query->selectRaw("*, ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance", [$lat, $lng, $lat])
                ->having('distance', '<', $radius)
                ->orderBy('distance', 'asc');
        }
        $kosts = $query->get();
        return response()->json($kosts);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'owner') return response()->json(['message' => 'Forbidden'], 403);
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'type' => 'required|in:putra,putri,campur',
        ]);
        $validated['owner_id'] = $user->id;
        $kost = Kost::create($validated);
        return response()->json($kost, 201);
    }

    public function show($id)
    {
        $kost = Kost::with(['rooms.photos', 'facilities'])->findOrFail($id);
        return response()->json($kost);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $kost = Kost::findOrFail($id);
        if ($kost->owner_id !== $user->id) return response()->json(['message' => 'Forbidden'], 403);
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'description' => 'sometimes|string',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string',
            'province' => 'sometimes|string',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'type' => 'sometimes|in:putra,putri,campur',
        ]);
        $kost->update($validated);
        return response()->json($kost);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $kost = Kost::findOrFail($id);
        if ($kost->owner_id !== $user->id) return response()->json(['message' => 'Forbidden'], 403);
        $kost->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function myKosts(Request $request)
    {
        $user = $request->user();
        $kosts = Kost::where('owner_id', $user->id)->with(['rooms.photos', 'facilities'])->get();
        return response()->json($kosts);
    }
}
