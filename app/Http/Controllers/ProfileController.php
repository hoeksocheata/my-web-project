<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'email'            => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => 'nullable|required_with:password|string',
            'password'         => 'nullable|min:6|confirmed',
        ], [
            'name.required'                => 'សូមបញ្ចូលឈ្មោះ',
            'email.unique'                 => 'អ៊ីមែលនេះត្រូវបានប្រើរួចហើយ',
            'current_password.required_with' => 'សូមបញ្ចូលលេខសម្ងាត់បច្ចុប្បន្ន ដើម្បីប្តូរលេខសម្ងាត់',
            'password.min'                 => 'លេខសម្ងាត់ត្រូវមានយ៉ាងតិច ៦ តួអក្សរ',
            'password.confirmed'           => 'លេខសម្ងាត់បញ្ជាក់មិនត្រូវគ្នា',
        ]);

        if (! empty($data['password'])) {
            if (! Hash::check($data['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'លេខសម្ងាត់បច្ចុប្បន្នមិនត្រឹមត្រូវ']);
            }
            $user->password = $data['password']; // cast 'hashed'
        }

        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->save();

        return back()->with('success', 'បានរក្សាទុកព័ត៌មានគណនី!');
    }
}
