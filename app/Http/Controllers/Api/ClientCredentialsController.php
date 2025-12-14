<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientAutoLoginCredential;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ClientCredentialsController extends Controller
{
    /**
     * Get client auto-login credentials
     * 
     * @return JsonResponse
     */
    public function getCredentials(): JsonResponse
    {
        try {
            $credentials = ClientAutoLoginCredential::first();

            if (!$credentials) {
                // Return default credentials if not in database
                return response()->json([
                    'email' => 'almajd@admin.com',
                    'password' => 'almajd123',
                ]);
            }

            return response()->json([
                'email' => $credentials->email,
                'password' => $credentials->getDecryptedPassword(),
            ]);
        } catch (\Exception $e) {
            // Fallback to default credentials on error
            return response()->json([
                'email' => 'almajd@admin.com',
                'password' => 'almajd123',
            ]);
        }
    }
}
