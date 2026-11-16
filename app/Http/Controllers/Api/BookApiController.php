<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class BookApiController extends Controller
{
    // API Register
    public function apiRegister(Request $request) {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user'
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json(['access_token' => $token, 'token_type' => 'Bearer'], 201);
    }

    // API Login យក Token
    public function apiLogin(Request $request) {
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json(['access_token' => $token, 'token_type' => 'Bearer']);
    }

    // បង្ហាញបញ្ជីសៀវភៅជា JSON
    public function index() {
        return response()->json(Book::with('category')->get(), 200);
    }

    // បន្ថែមសៀវភៅតាម API (លក្ខខណ្ឌសិទ្ធិ Admin)
    public function store(Request $request) {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden: Admin Only'], 403);
        }

        $book = Book::create($request->validate([
            'title' => 'required|string',
            'author' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'stock' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:2000',
        ]));

        return response()->json(['message' => 'Book created!', 'data' => $book], 201);
    }

    // លុបសៀវភៅតាម API (លក្ខខណ្ឌសិទ្ធិ Admin)
    public function destroy($id) {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden: Admin Only'], 403);
        }

        $book = Book::find($id);
        if (!$book) return response()->json(['message' => 'Book not found'], 404);

        $book->delete();
        return response()->json(['message' => 'Book deleted successfully!'], 200);
    }
}