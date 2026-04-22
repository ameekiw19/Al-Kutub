<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TwoFactorAuth extends Model
{
    use HasFactory;

    protected $table = 'two_factor_auths';
    
    protected $fillable = [
        'user_id',
        'secret_key',
        'backup_codes',
        'enabled_at',
        'last_used_at',
        'is_enabled'
    ];

    protected $casts = [
        'enabled_at' => 'datetime',
        'last_used_at' => 'datetime',
        'backup_codes' => 'array',
        'is_enabled' => 'boolean'
    ];

    /**
     * Generate secret key for Google Authenticator
     */
    public static function generateSecretKey()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 16; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Generate backup codes
     */
    public static function generateBackupCodes()
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(substr(md5(uniqid()), 0, 8));
        }
        return $codes;
    }

    /**
     * Build otpauth URL used by authenticator apps.
     */
    public static function generateQrCodeUrl($identifier, $secretKey, $issuer = 'Al-Kutub')
    {
        $params = [
            'secret' => $secretKey,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => '6',
            'period' => '30',
        ];

        return 'otpauth://totp/' . urlencode($issuer . ':' . $identifier) . '?' . http_build_query($params);
    }

    /**
     * Get current OTP based on secret key and time
     */
    public function getCurrentOTP()
    {
        if (!$this->secret_key) {
            return null;
        }
        
        $secretKey = base32_decode($this->secret_key);
        $time = floor(time() / 30);
        
        return $this->generateOTP($secretKey, $time);
    }

    /**
     * Generate OTP for given time slice
     */
    private function generateOTP($secret, $time)
    {
        $data = pack('N*', 0) . pack('N*', $time);
        $hash = hash_hmac('sha1', $data, $secret, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $binary = (
            (ord(substr($hash, $offset, 1)) & 0x7F) << 24 |
            (ord(substr($hash, $offset + 1, 1)) & 0xFF) << 16 |
            (ord(substr($hash, $offset + 2, 1)) & 0xFF) << 8 |
            (ord(substr($hash, $offset + 3, 1)) & 0xFF)
        );
        $otp = $binary % pow(10, 6);
        
        return str_pad($otp, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify OTP code
     */
    public function verifyOTP($code)
    {
        if (!$this->secret_key) {
            return false;
        }

        $secretKey = base32_decode($this->secret_key);
        $time = floor(time() / 30);
        $code = trim((string) $code);
        
        // Check current time and allow 1 time step before and after (90 seconds window)
        for ($i = -1; $i <= 1; $i++) {
            $otp = $this->generateOTP($secretKey, $time + $i);
            if ($otp === $code) {
                $this->last_used_at = now();
                $this->save();
                return true;
            }
        }
        
        return false;
    }

    /**
     * Compatibility alias used by API controllers.
     */
    public function verifyCode($code)
    {
        return $this->verifyOTP($code);
    }

    /**
     * Verify backup code
     */
    public function verifyBackupCode($code)
    {
        $backupCodes = $this->getBackupCodesArray();
        if (empty($backupCodes)) {
            return false;
        }

        $code = strtoupper(trim((string) $code));

        if (in_array($code, $backupCodes, true)) {
            // Remove used backup code
            $this->backup_codes = array_values(array_diff($backupCodes, [$code]));
            $this->last_used_at = now();
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Compatibility alias used by API controllers.
     */
    public function useBackupCode($code)
    {
        return $this->verifyBackupCode($code);
    }

    /**
     * Returns normalized backup codes for safe counting/processing.
     */
    public function getBackupCodesArray()
    {
        $value = $this->backup_codes;

        if (is_array($value)) {
            return array_values(array_filter(array_map(function ($item) {
                return strtoupper(trim((string) $item));
            }, $value), function ($item) {
                return $item !== '';
            }));
        }

        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return array_values(array_filter(array_map(function ($item) {
                    return strtoupper(trim((string) $item));
                }, $decoded), function ($item) {
                    return $item !== '';
                }));
            }
        }

        return [];
    }

    /**
     * Get Google Authenticator QR Code URL
     */
    public function getQRCodeUrl($appName, $username)
    {
        if (!$this->secret_key) {
            return null;
        }
        return self::generateQrCodeUrl($username, $this->secret_key, $appName);
    }

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

/**
 * Base32 decode function
 */
if (!function_exists('base32_decode')) {
    function base32_decode($base32String) {
        $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32String = strtoupper(preg_replace('/[^A-Z2-7]/', '', $base32String));
        
        $binaryString = '';
        $bits = 0;
        $value = 0;
        
        for ($i = 0; $i < strlen($base32String); $i++) {
            $char = $base32String[$i];
            $pos = strpos($base32Chars, $char);
            
            if ($pos === false) {
                continue;
            }
            
            $value = ($value << 5) | $pos;
            $bits += 5;
            
            if ($bits >= 8) {
                $binaryString .= chr(($value >> ($bits - 8)) & 0xFF);
                $bits -= 8;
            }
        }
        
        return $binaryString;
    }
}
