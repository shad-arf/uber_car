<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller
{
    /**
     * Register a new user
     */

     public function changePassword(Request $request)
    {
        // 1. Validate input
        $request->validate([
            'current_password'      => 'required|string',
            'new_password'          => 'required|string|min:6|confirmed',
            // 'new_password_confirmation' must match new_password
        ]);

        // 2. Get the authenticated user (via JWT token)
        $user = JWTAuth::parseToken()->authenticate();

        // 3. Check current password
        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'error' => 'Your current password does not match our records.'
            ], 400);
        }

        // 4. Update to new password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password changed successfully'
        ], 200);
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:15|unique:users',
            'birthday' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'driverlicense' => 'nullable|string',
            'role' => 'nullable|string|in:admin,user,manager,driver', // Adjust roles as needed
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'birthday' => $request->birthday,
            'gender' => $request->gender,
            'driverlicense' => $request->driverlicense,
            'role' => $request->role ?? 'user', // Default role to 'user'
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Login User and return JWT token
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'user' => auth()->user(),
            'token' => $token,
        ]);
    }

    /**
     * Logout user
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }


    /**
     * Refresh JWT Token
     */
    public function refresh()
    {
        return response()->json([
            'user' => auth()->user(),
            'token' => auth()->refresh()
        ]);
    }

    public function userProfile()
    {
        return response()->json(data: auth()->user());
    }

    // Get user by ID (admin only)
    public function getUserById($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user);
    }
    // Get all users (admin only)
    public function getAllUsers()
    {
        $users = User::all();
        return response()->json($users);
    }
    // admin can delete any user
    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
    //  promote user to admin
    public function promoteToAdmin($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->role = 'admin';
        $user->save();

        return response()->json(['message' => 'User promoted to admin successfully']);
    }
    // demote admin to user
    public function demoteToUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->role = 'user';
        $user->save();

        return response()->json(['message' => 'User demoted to user successfully']);
    }
}
