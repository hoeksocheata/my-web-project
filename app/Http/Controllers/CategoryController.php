<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('books')->orderBy('name')->get();
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
        ], [
            'name.required' => 'សូមបញ្ចូលឈ្មោះប្រភេទ',
            'name.unique'   => 'ប្រភេទនេះមានរួចហើយ',
        ]);

        Category::create($data);

        return back()->with('success', 'បានបន្ថែមប្រភេទថ្មី!');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
        ], [
            'name.required' => 'សូមបញ្ចូលឈ្មោះប្រភេទ',
            'name.unique'   => 'ប្រភេទនេះមានរួចហើយ',
        ]);

        $category->update($data);

        return back()->with('success', 'បានកែប្រែប្រភេទ!');
    }

    public function destroy(Category $category)
    {
        // សៀវភៅក្នុងប្រភេទនេះនឹងក្លាយជា "គ្មានប្រភេទ" (nullOnDelete)
        $category->delete();

        return back()->with('success', 'បានលុបប្រភេទរួចរាល់!');
    }
}
