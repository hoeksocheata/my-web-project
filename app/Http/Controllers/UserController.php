<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $users = User::query()
            ->when($search, fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")))
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->paginate(10)->withQueryString();

        return view('users.index', compact('users', 'search'));
    }

    public function updateRole(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'អ្នកមិនអាចប្តូរតួនាទីរបស់ខ្លួនឯងបានទេ។');
        }

        $data = $request->validate(['role' => ['required', Rule::in(['admin', 'user'])]]);
        $user->update($data);

        return back()->with('success', "បានប្តូរតួនាទីរបស់ {$user->name} ជា {$data['role']}។");
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'អ្នកមិនអាចលុបគណនីរបស់ខ្លួនឯងបានទេ។');
        }

        $user->tokens()->delete();
        $user->delete();

        return back()->with('success', 'បានលុបអ្នកប្រើប្រាស់រួចរាល់។');
    }
}
