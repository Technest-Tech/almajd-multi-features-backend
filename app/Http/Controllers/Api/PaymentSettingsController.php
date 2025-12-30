<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentSettings;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentSettingsController extends Controller
{
    /**
     * Get payment settings
     */
    public function index(): JsonResponse
    {
        $paypalEnabled = PaymentSettings::getSetting('paypal_enabled', '1');
        $anubpayEnabled = PaymentSettings::getSetting('anubpay_enabled', '0');

        return response()->json([
            'data' => [
                'paypal_enabled' => $paypalEnabled === '1',
                'anubpay_enabled' => $anubpayEnabled === '1',
            ],
        ]);
    }

    /**
     * Update payment settings
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'paypal_enabled' => 'nullable|boolean',
            'anubpay_enabled' => 'nullable|boolean',
        ]);

        if ($request->has('paypal_enabled')) {
            PaymentSettings::setSetting(
                'paypal_enabled',
                $request->input('paypal_enabled') ? '1' : '0',
                'Enable/Disable PayPal payment method'
            );
        }

        if ($request->has('anubpay_enabled')) {
            PaymentSettings::setSetting(
                'anubpay_enabled',
                $request->input('anubpay_enabled') ? '1' : '0',
                'Enable/Disable AnubPay payment method'
            );
        }

        return response()->json([
            'message' => 'Payment settings updated successfully',
            'data' => [
                'paypal_enabled' => PaymentSettings::getSetting('paypal_enabled', '1') === '1',
                'anubpay_enabled' => PaymentSettings::getSetting('anubpay_enabled', '0') === '1',
            ],
        ]);
    }

    /**
     * Get lesson settings
     */
    public function getLessonSettings(): JsonResponse
    {
        $teachersCanEditLessons = PaymentSettings::getSetting('teachers_can_edit_lessons', '0');
        $teachersCanDeleteLessons = PaymentSettings::getSetting('teachers_can_delete_lessons', '0');
        $teachersCanAddPastLessons = PaymentSettings::getSetting('teachers_can_add_past_lessons', '0');

        return response()->json([
            'data' => [
                'teachers_can_edit_lessons' => $teachersCanEditLessons === '1',
                'teachers_can_delete_lessons' => $teachersCanDeleteLessons === '1',
                'teachers_can_add_past_lessons' => $teachersCanAddPastLessons === '1',
            ],
        ]);
    }

    /**
     * Update lesson settings
     */
    public function updateLessonSettings(Request $request): JsonResponse
    {
        $request->validate([
            'teachers_can_edit_lessons' => 'nullable|boolean',
            'teachers_can_delete_lessons' => 'nullable|boolean',
            'teachers_can_add_past_lessons' => 'nullable|boolean',
        ]);

        if ($request->has('teachers_can_edit_lessons')) {
            PaymentSettings::setSetting(
                'teachers_can_edit_lessons',
                $request->input('teachers_can_edit_lessons') ? '1' : '0',
                'Allow teachers to edit lessons'
            );
        }

        if ($request->has('teachers_can_delete_lessons')) {
            PaymentSettings::setSetting(
                'teachers_can_delete_lessons',
                $request->input('teachers_can_delete_lessons') ? '1' : '0',
                'Allow teachers to delete lessons'
            );
        }

        if ($request->has('teachers_can_add_past_lessons')) {
            PaymentSettings::setSetting(
                'teachers_can_add_past_lessons',
                $request->input('teachers_can_add_past_lessons') ? '1' : '0',
                'Allow teachers to add lessons in past dates'
            );
        }

        return response()->json([
            'message' => 'Lesson settings updated successfully',
            'data' => [
                'teachers_can_edit_lessons' => PaymentSettings::getSetting('teachers_can_edit_lessons', '0') === '1',
                'teachers_can_delete_lessons' => PaymentSettings::getSetting('teachers_can_delete_lessons', '0') === '1',
                'teachers_can_add_past_lessons' => PaymentSettings::getSetting('teachers_can_add_past_lessons', '0') === '1',
            ],
        ]);
    }
}
