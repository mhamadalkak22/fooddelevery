<?php
namespace App\Http\Controllers;

use App\Models\FoodItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FoodItemController extends Controller
{
    public function index()
    {
        // Only authenticated users can view the list of food items
        if (Auth::check()) {
            $foodItems = FoodItem::with('category')->paginate();
            return response()->json($foodItems);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }

    /**
     * Store a newly created food item in storage.
     */
    public function store(Request $request)
    {
        \Log::info('Store method started.');
    
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'is_available' => 'sometimes|boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        \Log::info('Validation passed.');
    
        // Store the photo if provided
        $photoPath = null;
        if ($request->hasFile('photo')) {
            \Log::info('Uploading photo.');
            $photoPath = $request->file('photo')->store('photos', 'public');
            \Log::info('Photo uploaded to: ' . $photoPath);
        }
    
        \Log::info('Creating FoodItem.');
        // Create and return the food item
        $foodItem = FoodItem::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'user_id' => Auth::id(),
            'is_available' => $request->get('is_available', true),
            'photo' => $photoPath,
        ]);
    
        \Log::info('FoodItem created: ', $foodItem->toArray());
    
        return response()->json(['message' => 'Food item created successfully', 'food_item' => $foodItem], 201);
    }
    /**
     * Display the specified food item.
     */
    public function show(FoodItem $foodItem)
    {
        // Anyone can view a specific food item if they are authenticated
        if (Auth::check()) {
            $foodItem->load(['category', 'user']);
            return response()->json($foodItem);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }

    /**
     * Update the specified food item in storage.
     */
    public function update(Request $request, FoodItem $foodItem)
    {
        // Only admin and chef can update food items
        if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('chef')) {
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'price' => 'sometimes|numeric|min:0',
                'category_id' => 'sometimes|exists:categories,id',
                'is_available' => 'sometimes|boolean',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validation for photo
            ]);

            $photoPath = $foodItem->photo;
            if ($request->hasFile('photo')) {
                // Delete the old photo if it exists
                if ($photoPath) {
                    Storage::disk('public')->delete($photoPath);
                }
                // Store the new photo
                $photoPath = $request->file('photo')->store('photos', 'public');
            }

            $foodItem->update([
                'name' => $request->name ?? $foodItem->name,
                'description' => $request->description ?? $foodItem->description,
                'price' => $request->price ?? $foodItem->price,
                'category_id' => $request->category_id ?? $foodItem->category_id,
                'is_available' => $request->is_available ?? $foodItem->is_available,
                'photo' => $photoPath,
            ]);

            return response()->json(['message' => 'Food item updated successfully', 'food_item' => $foodItem]);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }

    /**
     * Remove the specified food item from storage.
     */
    public function destroy(FoodItem $foodItem)
    {
        // Allow admin or chef to delete food items
        if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('chef')) {
            // Delete the photo if it exists
            if ($foodItem->photo) {
                Storage::disk('public')->delete($foodItem->photo);
            }
            $foodItem->delete();
            return response()->json(['message' => 'Food item deleted successfully']);
        }
    
        return response()->json(['error' => 'Unauthorized'], 403);
    }
}
