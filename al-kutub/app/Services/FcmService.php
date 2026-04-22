<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private $project_id;
    private $auth_file;

    public function __construct()
    {
        $this->auth_file = storage_path('app/firebase-auth.json');
        
        // Load project_id from file if exists
        if (file_exists($this->auth_file)) {
            $authData = json_decode(file_get_contents($this->auth_file), true);
            $this->project_id = $authData['project_id'] ?? null;
        }

        $this->fcmUrl = "https://fcm.googleapis.com/v1/projects/{$this->project_id}/messages:send";
    }

    /**
     * Generate Google OAuth2 Access Token manually using Service Account
     * Avoids installing heavy google/apiclient dependency
     */
    private function getAccessToken()
    {
        if (!file_exists($this->auth_file)) {
            \Log::error('Firebase auth file not found at ' . $this->auth_file);
            return null;
        }

        $authData = json_decode(file_get_contents($this->auth_file), true);
        $privateKey = $authData['private_key'];
        $clientEmail = $authData['client_email'];

        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $now = time();
        $payload = json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/cloud-platform',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = '';
        openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        try {
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]);

            return $response->json()['access_token'] ?? null;
        } catch (\Exception $e) {
            \Log::error('Failed to get FCM access token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send notification to specific device token
     */
    public function sendToDevice($deviceToken, $title, $body, $data = [])
    {
        $payload = $this->buildPayload($title, $body, $data);
        $payload['message']['token'] = $deviceToken;

        return $this->send($payload);
    }

    /**
     * Send notification to multiple devices (No native multicast in V1, sending individually)
     */
    public function sendToDevices($deviceTokens, $title, $body, $data = [])
    {
        $results = [];
        foreach ($deviceTokens as $token) {
            $results[] = $this->sendToDevice($token, $title, $body, $data);
        }
        return $results;
    }

    /**
     * Send notification to topic
     */
    public function sendToTopic($topic, $title, $body, $data = [])
    {
        $payload = $this->buildPayload($title, $body, $data);
        $payload['message']['topic'] = $topic;

        return $this->send($payload);
    }

    /**
     * Build unified FCM V1 payload
     */
    private function buildPayload($title, $body, $data = [])
    {
        return [
            'message' => [
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'data' => !empty($data) ? array_map('strval', $data) : (object)[],
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'channel_id' => 'new_kitab_channel',
                        'sound' => 'default',
                        'click_action' => $data['action_url'] ?? null,
                        'default_vibrate_timings' => true,
                        'default_light_settings' => true,
                        'notification_priority' => 'PRIORITY_HIGH'
                    ]
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1,
                        ],
                    ],
                ]
            ]
        ];
    }

    /**
     * Send notification to all users (broadcast)
     */
    public function sendToAll($title, $body, $data = [])
    {
        // 1. Send to Topic (Efficient for large groups)
        $topicResult = $this->sendToTopic('all_users', $title, $body, $data);
        
        // 2. Send to all active device tokens (Fallback for unreliable topic subscription)
        $tokens = \App\Models\FcmToken::getActiveTokens();
        if (!empty($tokens)) {
            \Log::info("FCM Broadcast Fallback: Attempting to send to " . count($tokens) . " active tokens");
            $tokenResults = $this->sendToDevices($tokens, $title, $body, $data);
            
            \Log::info('FCM Broadcast Fallback Result', [
                'token_count' => count($tokens),
                'topic_success' => $topicResult['success'] ?? false,
                'token_results' => $tokenResults
            ]);
        } else {
            \Log::warning('FCM Broadcast Fallback: No active device tokens found in database');
        }

        return $topicResult;
    }

    /**
     * Send notification when new kitab is added
     */
    public function sendNewKitabNotification($kitab)
    {
        $title = 'Kitab Baru Tersedia!';
        $body = "Kitab '{$kitab->judul}' oleh {$kitab->penulis} telah ditambahkan. Yuk baca sekarang!";
        
        $data = [
            'type' => 'new_kitab',
            'kitab_id' => (string)$kitab->id_kitab,
            'judul' => (string)$kitab->judul,
            'penulis' => (string)$kitab->penulis,
            'action_url' => "/kitab/{$kitab->id_kitab}"
        ];

        return $this->sendToAll($title, $body, $data);
    }

    /**
     * Send HTTP request to FCM V1
     */
    private function send($payload)
    {
        try {
            $accessToken = $this->getAccessToken();
            
            if (!$accessToken) {
                return ['success' => false, 'error' => 'Could not generate access token'];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->post($this->fcmUrl, $payload);

            $responseData = $response->json();

            \Log::info('FCM V1 Notification Sent', [
                'project_id' => $this->project_id,
                'response' => $responseData,
                'status_code' => $response->status()
            ]);

            return [
                'success' => $response->successful(),
                'data' => $responseData,
                'status_code' => $response->status()
            ];

        } catch (\Exception $e) {
            \Log::error('FCM V1 Notification Failed', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Subscribe device to topic (Still uses Legacy IID endpoint, but works with V1)
     */
    public function subscribeToTopic($deviceToken, $topic)
    {
        // For subscription we still need the old server key or we can try using the new access token
        // But the IID endpoint specifically prefers the Server Key. 
        // We'll keep it as-is if the user keeps the key in .env
        $serverKey = config('services.fcm.server_key');
        $url = "https://iid.googleapis.com/iid/v1/{$deviceToken}/rel/topics/{$topic}";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json'
            ])->post($url);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
