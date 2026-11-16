<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /** អតិថិជនដែលធ្លាប់ទិញសៀវភៅនេះ (ការកម្មង់ត្រូវបានអនុម័ត) ទើបវាយតម្លៃបាន */
    public function store(Request $request, Book $book)
    {
        if (! $request->user()->hasPurchased($book)) {
            return back()->with('error', 'អ្នកអាចវាយតម្លៃបានតែសៀវភៅដែលអ្នកបានទិញប៉ុណ្ណោះ។');
        }

        $data = $request->validate([
            'rating'  => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:500',
        ], [
            'rating.required' => 'សូមជ្រើសរើសផ្កាយ',
            'rating.between'  => 'ផ្កាយត្រូវនៅចន្លោះ 1 ដល់ 5',
        ]);

        Review::updateOrCreate(
            ['book_id' => $book->id, 'user_id' => $request->user()->id],
            $data
        );

        return back()->with('success', 'សូមអរគុណសម្រាប់ការវាយតម្លៃ!');
    }

    public function destroy(Request $request, Review $review)
    {
        abort_unless($request->user()->isAdmin() || $review->user_id === $request->user()->id, 403);

        $review->delete();

        return back()->with('success', 'បានលុបការវាយតម្លៃ។');
    }
}
