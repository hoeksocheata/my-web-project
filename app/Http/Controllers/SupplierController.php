<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    private const RULES = [
        'name'    => 'required|string|max:150',
        'phone'   => 'nullable|string|max:30',
        'address' => 'nullable|string|max:255',
    ];

    private const MESSAGES = ['name.required' => 'សូមបញ្ចូលឈ្មោះអ្នកផ្គត់ផ្គង់'];

    public function index()
    {
        $suppliers = Supplier::withCount('purchases')->orderBy('name')->get();

        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        Supplier::create($request->validate(self::RULES, self::MESSAGES));

        return back()->with('success', 'បានបន្ថែមអ្នកផ្គត់ផ្គង់!');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($request->validate(self::RULES, self::MESSAGES));

        return back()->with('success', 'បានកែប្រែអ្នកផ្គត់ផ្គង់!');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete(); // ការទិញចូលចាស់ៗនៅរក្សាទុក (supplier_id ទៅជា null)

        return back()->with('success', 'បានលុបអ្នកផ្គត់ផ្គង់រួចរាល់!');
    }
}
