<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Session;

class Register extends Controller
{
     public function register()
    {
        return view('Register');
    }
    
    public function actionregister(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email|max:255|unique:users,email',
            'username' => 'required|string|min:3|max:255|unique:users,username',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'description' => 'nullable|string|max:500',
        ]);

        $user = User::create([
            'email' => $validated['email'],
            'username' => $validated['username'],
            'deskripsi' => $validated['description'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'email_verified_at' => null,
            'is_verified_by_admin' => false,
            'admin_verified_at' => null,
            'admin_verified_by' => null,
        ]);

        Session::flash('status', 'Registrasi berhasil. Akun Anda menunggu verifikasi admin sebelum bisa login.');

        return redirect()->route('login');
    }
}
