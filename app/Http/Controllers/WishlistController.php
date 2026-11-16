<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $books = $request->user()->wishlist()->with('category')->orderByPivot('created_at', 'desc')->paginate(12);

        return view('wishlist.index', compact('books'));
    }

    public function toggle(Request $request, Book $book)
    {
        $result = $request->user()->wishlist()->toggle($book->id);
        $added  = ! empty($result['attached']);

        return back()->with('success', $added ? "បានបន្ថែម «{$book->title}» ទៅបញ្ជីចូលចិត្ត" : "បានដក «{$book->title}» ចេញពីបញ្ជីចូលចិត្ត");
    }
}
