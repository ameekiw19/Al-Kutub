<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Support\VerificationTokenService;
use Illuminate\Http\Request;

class ApiEmailVerificationController extends Controller
{
    public function resend(Request $request)
    {
        $request->validate([
            'verification_token' => 'required|string',
        ]);

        $user = VerificationTokenService::resolve($request->input('verification_token'));

        if ($user && !$user->hasVerifiedEmail()) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                report($e);

                return response()->json([
                    'success' => false,
                    'message' => 'Layanan email sedang bermasalah. Silakan coba beberapa saat lagi.',
                ], 503);
            }

            AuditLog::logAuth('email_verification_resent', $user->id, [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Jika akun valid dan belum terverifikasi, link verifikasi telah dikirim ke email.',
        ]);
    }

    public function status(Request $request)
    {
        $request->validate([
            'verification_token' => 'required|string',
        ]);

        $user = VerificationTokenService::resolve($request->input('verification_token'));

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token verifikasi tidak valid atau kadaluarsa.',
                'data' => [
                    'verified' => false,
                ],
            ], 400);
        }

        $verified = $user->hasVerifiedEmail();

        return response()->json([
            'success' => true,
            'message' => $verified ? 'Email sudah terverifikasi.' : 'Email belum terverifikasi.',
            'data' => [
                'verified' => $verified,
                'email' => $user->email,
                'requires_email_verification' => !$verified,
                'verification_token' => $verified ? null : VerificationTokenService::issue($user),
            ],
        ]);
    }
}
