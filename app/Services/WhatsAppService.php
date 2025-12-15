<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private const API_URL = 'https://whatsapp.almajd.info/api/send-message';
    private const API_KEY = 'wa_0930233a358c41b9a22588b86f0d8ff4';

    /**
     * Send a WhatsApp message
     *
     * @param string $to Phone number in format: 201554134201@c.us
     * @param string $message Message content
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendMessage(string $to, string $message): array
    {
        try {
            // Clean and format phone number
            // Remove any existing @c.us suffix first
            $to = str_replace('@c.us', '', $to);
            
            // Remove any non-digit characters (spaces, dashes, plus signs, etc.)
            $to = preg_replace('/\D/', '', $to);
            
            // Ensure phone number has @c.us suffix
            if (!empty($to)) {
                $to = $to . '@c.us';
            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid phone number format',
                ];
            }

            $response = Http::withHeaders([
                'X-API-Key' => self::API_KEY,
                'Content-Type' => 'application/json',
            ])->post(self::API_URL, [
                'to' => $to,
                'message' => $message,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'data' => $response->json(),
                ];
            }

            $errorMessage = $response->json()['message'] ?? 'Failed to send message';
            Log::error('WhatsApp API Error', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => $errorMessage,
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp Service Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage(),
            ];
        }
    }
}

