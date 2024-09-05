<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class CategoryController extends Controller
{


    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        $user = auth()->user();
    
        if ($user->hasRole('admin')) {
           
            $categories = Category::with('foodItems')->get();
        } elseif ($user->hasRole('chef')) {
           
            $categories = Category::with('foodItems')->get();
        } else {
            
            $categories = Category::all();
        }
    
        return response()->json($categories);
    }
    

   
    public function store(Request $request)
    {
        
        if (auth()->user()->hasRole('admin') || auth()->user()->can('manage categories')) {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $category = Category::create($request->all());

            return response()->json(['message' => 'Category created successfully', 'category' => $category], 201);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }

    public function show(Category $category)
    {
        
        $category->load('foodItems');
        return response()->json($category);
    }

   
    public function update(Request $request, Category $category)
    {
      
        if (auth()->user()->hasRole('admin') || auth()->user()->can('manage categories')) {
      
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
            ]);
    
      
            $category->update($request->only(['name', 'description']));
    
            return response()->json(['message' => 'Category updated successfully', 'category' => $category]);
        }
    
        return response()->json(['error' => 'Unauthorized'], 403);
    }

   
    public function destroy(Category $category)
    {
        // Check if the user has the 'admin' role or the 'manage categories' permission
        if (auth()->user()->hasRole('admin') || auth()->user()->can('manage categories')) {
            // Delete the category
            $category->delete();
    
            return response()->json(['message' => 'Category deleted successfully']);
        }
    
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
}
