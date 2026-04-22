<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ApiPasswordResetController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            Password::broker()->sendResetLink([
                'email' => $request->input('email'),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Layanan email sedang bermasalah. Silakan coba lagi.',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'message' => 'Jika email terdaftar, kami telah mengirim link reset password.',
        ]);
    }
}
