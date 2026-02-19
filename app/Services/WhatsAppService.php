<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a WhatsApp message via Wasender API.
     *
     * @param string $to Phone number (e.g. 201554134201, +201554134201, or with @c.us / @s.whatsapp.net)
     * @param string $message Message content
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendMessage(string $to, string $message): array
    {
        $apiUrl = config('services.wasender.api_url', 'https://wasenderapi.com');
        $apiKey = config('services.wasender.api_key');

        if (empty($apiKey)) {
            Log::error('WhatsApp (Wasender): WASENDER_API_KEY is not set in .env');
            return [
                'success' => false,
                'message' => 'WhatsApp API key not configured',
            ];
        }

        try {
            $to = $this->normalizeToJid($to);
            if (empty($to)) {
                return [
                    'success' => false,
                    'message' => 'Invalid phone number format',
                ];
            }

            $url = rtrim($apiUrl, '/') . '/api/send-message';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post($url, [
                    'to' => $to,
                    'text' => $message,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'data' => $response->json(),
                ];
            }

            $body = $response->json();
            $errorMessage = $body['message'] ?? $body['error'] ?? $response->body() ?: 'Failed to send message';
            Log::error('Wasender API Error', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => is_string($errorMessage) ? $errorMessage : 'Failed to send message',
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp (Wasender) Service Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Normalize phone number to Wasender JID format.
     * Wasender expects RECIPIENT_JID (e.g. 201234567890@s.whatsapp.net).
     */
    private function normalizeToJid(string $to): string
    {
        $to = str_replace(['@c.us', '@s.whatsapp.net'], '', $to);
        $to = preg_replace('/\D/', '', $to);
        if (empty($to)) {
            return '';
        }
        return $to . '@s.whatsapp.net';
    }
}
