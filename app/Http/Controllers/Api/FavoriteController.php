<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Kost;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $favorites = $user->favorites()->with(['kost.owner', 'kost.rooms.photos', 'kost.facilities'])->get();
        return response()->json($favorites);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $request->validate(['kost_id' => 'required|exists:kosts,id']);
        $favorite = Favorite::where('user_id', $user->id)->where('kost_id', $request->kost_id)->first();
        if ($favorite) {
            $favorite->delete();
            return response()->json(['message' => 'Removed from favorites']);
        } else {
            Favorite::create(['user_id' => $user->id, 'kost_id' => $request->kost_id]);
            return response()->json(['message' => 'Added to favorites']);
        }
    }

    public function destroy(Request $request, $kost_id)
    {
        $user = $request->user();
        $favorite = Favorite::where('user_id', $user->id)->where('kost_id', $kost_id)->first();
        if ($favorite) {
            $favorite->delete();
            return response()->json(['message' => 'Removed from favorites']);
        }
        return response()->json(['message' => 'Not found'], 404);
    }
}
