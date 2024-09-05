<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'address' => $request->address,  
            'phone_number' => $request->phone_number,  
        ]);
        $user->assignRole($request->role);

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    public function login(Request $request)
    {
        // Validate the login request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if the user exists and the password is correct
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Create a token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return the token in the response
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
    public function logout(Request $request)
    {
        // Revoke all tokens the user has
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }


    public function getProfile(Request $request)
    {
        return response()->json($request->user());
    }

    public function update(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $request->user()->id,
            'password' => 'sometimes|string|min:8|confirmed',
            'address' => 'sometimes|string|max:255',
            'phone_number' => 'sometimes|string|max:15',
        ]);

      
        $user = $request->user();

      
        $user->fill($request->only(['name', 'email', 'address', 'phone_number']));

       
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json(['message' => 'Profile updated successfully']);
    }

}
