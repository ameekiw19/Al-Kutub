<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\VerificationTokenService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    public function notice(Request $request)
    {
        $verificationToken = (string) ($request->query('token') ?? session('verification_token', ''));
        $email = (string) ($request->query('email') ?? session('verification_email', ''));

        if ($verificationToken === '' && Auth::check() && !Auth::user()->hasVerifiedEmail()) {
            $verificationToken = VerificationTokenService::issue(Auth::user());
        }

        if ($email === '' && Auth::check()) {
            $email = (string) Auth::user()->email;
        }

        return view('auth.verify-email-notice', [
            'verificationToken' => $verificationToken,
            'email' => $email,
        ]);
    }

    public function resend(Request $request)
    {
        $request->validate([
            'verification_token' => 'required|string',
        ]);

        $user = VerificationTokenService::resolve($request->input('verification_token'));
        if (!$user && Auth::check() && !Auth::user()->hasVerifiedEmail()) {
            $user = Auth::user();
        }
        $nextToken = (string) $request->input('verification_token');
        $email = '';
        $message = 'Jika akun valid dan belum terverifikasi, link verifikasi telah dikirim ke email.';

        if ($user) {
            $email = $user->email;
            if (!$user->hasVerifiedEmail()) {
                try {
                    $user->sendEmailVerificationNotification();
                } catch (\Throwable $e) {
                    report($e);

                    return redirect()
                        ->route('verification.notice', ['token' => $nextToken, 'email' => $email])
                        ->with('error', 'Layanan email sedang bermasalah. Silakan coba lagi.');
                }

                $nextToken = VerificationTokenService::issue($user);
                $message = 'Link verifikasi berhasil dikirim ulang ke email Anda.';

                AuditLog::logAuth('email_verification_resent', $user->id, [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            } else {
                $message = 'Email Anda sudah terverifikasi. Silakan login.';
            }
        }

        return redirect()
            ->route('verification.notice', ['token' => $nextToken, 'email' => $email])
            ->with('status', $message);
    }

    public function verify(Request $request, $id, $hash)
    {
        /** @var User|null $user */
        $user = User::find($id);

        if (!$user) {
            return redirect()->route('login')->with('error', 'Akun tidak ditemukan.');
        }

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('login')->with('error', 'Link verifikasi tidak valid.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('message', 'Email sudah terverifikasi. Silakan login.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        AuditLog::logAuth('email_verified', $user->id, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('login')->with('message', 'Email berhasil diverifikasi. Silakan login.');
    }
}
