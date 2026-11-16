<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WebAuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }

    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ], [
            'email.required' => 'សូមបញ្ចូលអ៊ីមែល',
            'email.email' => 'ទម្រង់អ៊ីមែលមិនត្រឹមត្រូវ',
            'password.required' => 'សូមបញ្ចូលលេខសម្ងាត់',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/books');
        }

        return back()->withInput($request->only('email'))
                     ->withErrors(['email' => 'អ៊ីមែល ឬលេខសម្ងាត់មិនត្រឹមត្រូវ!']);
    }

    public function showRegister() { return view('auth.register'); }

    public function register(Request $request) {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed'
        ], [
            'name.required' => 'សូមបញ្ចូលឈ្មោះ',
            'email.required' => 'សូមបញ្ចូលអ៊ីមែល',
            'email.email' => 'ទម្រង់អ៊ីមែលមិនត្រឹមត្រូវ',
            'email.unique' => 'អ៊ីមែលនេះត្រូវបានប្រើរួចហើយ',
            'password.required' => 'សូមបញ្ចូលលេខសម្ងាត់',
            'password.min' => 'លេខសម្ងាត់ត្រូវមានយ៉ាងតិច ៦ តួអក្សរ',
            'password.confirmed' => 'លេខសម្ងាត់បញ្ជាក់មិនត្រូវគ្នា',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, // User model មាន cast 'hashed' រួចហើយ
            'role' => 'user' // ចុះឈ្មោះដំបូងជា User ធម្មតា
        ]);

        return redirect()->route('login')->with('success', 'ចុះឈ្មោះជោគជ័យ! សូមចូលគណនី។');
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
