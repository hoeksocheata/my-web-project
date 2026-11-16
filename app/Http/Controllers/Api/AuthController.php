<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request) {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|min:6'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password'])
        ]);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('myapptoken')->plainTextToken
        ], 201);
    }

    public function login(Request $request) {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $fields['email'])->first();

        if(!$user || !Hash::check($fields['password'], $user->password)) {
            return response()->json(['message' => 'អ៊ីមែល ឬលេខសម្ងាត់មិនត្រឹមត្រូវ'], 401);
        }

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('myapptoken')->plainTextToken
        ], 200);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'បានចាកចេញដោយជោគជ័យ!']);
    }
}