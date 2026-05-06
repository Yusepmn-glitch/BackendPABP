<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kost;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function nearby(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'nullable|numeric',
        ]);
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $request->radius ?? 5;
        $kosts = Kost::selectRaw("
            *,
            ( 6371 * acos( cos( radians(?) ) *
              cos( radians( latitude ) ) *
              cos( radians( longitude ) - radians(?) ) +
              sin( radians(?) ) *
              sin( radians( latitude ) ) )
            ) AS distance
        ", [$latitude, $longitude, $latitude])
        ->having('distance', '<', $radius)
        ->orderBy('distance', 'asc')
        ->with(['owner', 'rooms.photos', 'facilities'])
        ->get();
        return response()->json($kosts);
    }

    public function filter(Request $request)
    {
        $query = Kost::with(['owner', 'rooms.photos', 'facilities']);
        if ($request->city) $query->where('city', $request->city);
        if ($request->type) $query->where('type', $request->type);
        if ($request->min_price) $query->whereHas('rooms', fn($q) => $q->where('price', '>=', $request->min_price));
        if ($request->max_price) $query->whereHas('rooms', fn($q) => $q->where('price', '<=', $request->max_price));
        if ($request->facilities) {
            $facilityIds = is_array($request->facilities) ? $request->facilities : explode(',', $request->facilities);
            $query->whereHas('facilities', fn($q) => $q->whereIn('facilities.id', $facilityIds));
        }
        $kosts = $query->get();
        return response()->json($kosts);
    }
}
