<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $settings;

    public function __construct()
    {
        $this->settings = $this->getSettings();
    }

    public function sendPushNotification($userId, $title, $body, $data = [])
    {
        if (($this->settings['enable_push_notifications'] ?? 'off') !== 'on') {
            return false;
        }

        $user = User::find($userId);
        if (!$user || !$user->fcm_token) {
            return false;
        }

        $projectId = $this->settings['fcm_project_id'] ?? '';
        if (empty($projectId)) {
            Log::error('FCM Project ID is missing in settings.');
            return false;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            Log::error('Failed to generate FCM Access Token.');
            return false;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        // Convert all data values to strings for FCM V1
        $formattedData = [];
        foreach ($data as $key => $value) {
            $formattedData[(string)$key] = (string)$value;
        }

        $payload = [
            'message' => [
                'token' => $user->fcm_token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $formattedData,
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        if (!$response->successful()) {
            Log::error('FCM V1 Error: ' . $response->body());
        }

        return $response->successful();
    }

    protected function getAccessToken()
    {
        $cacheKey = 'fcm_access_token';
        if (cache()->has($cacheKey)) {
            return cache()->get($cacheKey);
        }

        $jsonPath = 'certs/fcm-service-account.json';
        if (!Storage::disk('local')->exists($jsonPath)) {
            Log::error('FCM Service Account JSON not found at ' . $jsonPath);
            return null;
        }

        $serviceAccount = json_decode(Storage::disk('local')->get($jsonPath), true);
        
        $now = time();
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $payload = [
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now,
        ];

        $base64UrlHeader = $this->base64UrlEncode(json_encode($header));
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));

        $signature = '';
        $success = openssl_sign(
            $base64UrlHeader . "." . $base64UrlPayload,
            $signature,
            $serviceAccount['private_key'],
            'SHA256'
        );

        if (!$success) {
            return null;
        }

        $base64UrlSignature = $this->base64UrlEncode($signature);
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if ($response->successful()) {
            $accessToken = $response->json('access_token');
            $expiresIn = $response->json('expires_in', 3600);
            cache()->put($cacheKey, $accessToken, $expiresIn - 60);
            return $accessToken;
        }

        Log::error('FCM Token Exchange Error: ' . $response->body());
        return null;
    }

    protected function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    protected function getSettings()
    {
        $file = 'settings.json';
        if (!Storage::disk('local')->exists($file)) {
            return [];
        }
        return json_decode(Storage::disk('local')->get($file), true);
    }
}
