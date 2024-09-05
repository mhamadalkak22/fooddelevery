<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\FoodItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoritesController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $favorites = $user->favoriteFoodItems()->with('category')->get();

        return response()->json($favorites);
    }
    public function store(Request $request)
    {
        $request->validate([
            'food_item_id' => 'required|exists:food_items,id',
        ]);

        $user = Auth::user();

        // Check if the food item is already in the user's favorites
        if ($user->favoriteFoodItems()->where('food_item_id', $request->food_item_id)->exists()) {
            return response()->json(['message' => 'Food item is already in favorites'], 200);
        }

        // Add the food item to the user's favorites
        $user->favoriteFoodItems()->attach($request->food_item_id);

        return response()->json(['message' => 'Food item added to favorites successfully']);
    }
    public function destroy($foodItemId)
    {
        $user = Auth::user();

        // Check if the food item is in the user's favorites
        if (!$user->favoriteFoodItems()->where('food_item_id', $foodItemId)->exists()) {
            return response()->json(['message' => 'Food item is not in favorites'], 404);
        }

        // Remove the food item from the user's favorites
        $user->favoriteFoodItems()->detach($foodItemId);

        return response()->json(['message' => 'Food item removed from favorites successfully']);
    }

}
