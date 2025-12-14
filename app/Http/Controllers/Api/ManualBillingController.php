<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ManualBilling;
use App\Models\User;
use App\Services\BillingService;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ManualBillingController extends Controller
{
    public function __construct(
        private BillingService $billingService,
        private WhatsAppService $whatsAppService
    ) {}

    /**
     * List all manual billings
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->input('search');
        
        $query = ManualBilling::with('creator');

        // Filter by student name if search provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                // Search in student_ids JSON array
                $q->whereRaw('JSON_CONTAINS(student_ids, ?)', [json_encode((int)$search)])
                  ->orWhereRaw('JSON_SEARCH(student_ids, "one", ?) IS NOT NULL', ["%{$search}%"]);
            });
        }

        $billings = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Load students for each billing manually
        $billings->getCollection()->transform(function ($billing) {
            $studentIds = $billing->student_ids ?? [];
            if (!empty($studentIds)) {
                $billing->setRelation('students', User::whereIn('id', $studentIds)->get());
            } else {
                $billing->setRelation('students', collect([]));
            }
            return $billing;
        });

        return response()->json($billings);
    }

    /**
     * Create new manual billing
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'integer|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|in:USD,GBP,EUR,EGP,SAR,AED,CAD',
            'message' => 'nullable|string|max:1000',
        ]);

        try {
            $billing = ManualBilling::create([
                'student_ids' => $request->input('student_ids'),
                'amount' => $request->input('amount'),
                'currency' => $request->input('currency'),
                'message' => $request->input('message'),
                'created_by' => Auth::id(),
                'is_paid' => false,
            ]);

            // Generate payment token
            $billing->generatePaymentToken();
            
            // Load students manually
            $studentIds = $billing->student_ids ?? [];
            if (!empty($studentIds)) {
                $billing->setRelation('students', User::whereIn('id', $studentIds)->get());
            } else {
                $billing->setRelation('students', collect([]));
            }
            $billing->load('creator');

            return response()->json([
                'data' => $billing,
                'message' => 'Manual billing created successfully',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating manual billing: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create manual billing'], 500);
        }
    }

    /**
     * Get billing details
     */
    public function show(string $id): JsonResponse
    {
        $billing = ManualBilling::with('creator')->findOrFail($id);
        
        // Load students manually
        $studentIds = $billing->student_ids ?? [];
        if (!empty($studentIds)) {
            $billing->setRelation('students', \App\Models\User::whereIn('id', $studentIds)->get());
        } else {
            $billing->setRelation('students', collect([]));
        }

        return response()->json([
            'data' => $billing,
            'message' => 'Billing retrieved successfully',
        ]);
    }

    /**
     * Update manual billing
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $billing = ManualBilling::findOrFail($id);

        $request->validate([
            'student_ids' => 'sometimes|array',
            'student_ids.*' => 'integer|exists:users,id',
            'amount' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|in:USD,GBP,EUR,EGP,SAR,AED,CAD',
            'message' => 'nullable|string|max:1000',
        ]);

        try {
            $billing->update($request->only(['student_ids', 'amount', 'currency', 'message']));
            $billing->refresh();
            
            // Load students manually
            $studentIds = $billing->student_ids ?? [];
            if (!empty($studentIds)) {
                $billing->setRelation('students', User::whereIn('id', $studentIds)->get());
            } else {
                $billing->setRelation('students', collect([]));
            }
            $billing->load('creator');

            return response()->json([
                'data' => $billing,
                'message' => 'Manual billing updated successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating manual billing: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update manual billing'], 500);
        }
    }

    /**
     * Delete manual billing
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $billing = ManualBilling::findOrFail($id);
            $billing->delete();

            return response()->json([
                'message' => 'Manual billing deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting manual billing: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete manual billing'], 500);
        }
    }

    /**
     * Mark billing as paid manually
     */
    public function markAsPaid(Request $request, string $id): JsonResponse
    {
        try {
            $this->billingService->markAsPaid($id, 'manual', 'manual');

            return response()->json([
                'message' => 'Billing marked as paid successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking billing as paid: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to mark billing as paid'], 500);
        }
    }

    /**
     * Send payment link via WhatsApp
     */
    public function sendWhatsApp(Request $request, string $id): JsonResponse
    {
        try {
            $billing = ManualBilling::findOrFail($id);
            $studentIds = $billing->student_ids ?? [];
            
            if (empty($studentIds)) {
                return response()->json(['error' => 'No students found for this billing'], 400);
            }
            
            $students = User::whereIn('id', $studentIds)->get();

            if ($students->isEmpty()) {
                return response()->json(['error' => 'No students found for this billing'], 400);
            }

            // Generate payment link if not exists
            if (!$billing->payment_token) {
                $billing->generatePaymentToken();
            }

            $paymentLink = url("/pay/{$billing->payment_token}");
            $currencySymbol = $billing->currency instanceof \App\Enums\Currency 
                ? $billing->currency->symbol() 
                : $billing->currency;
            $studentNames = $students->pluck('name')->join(', ');

            $message = "Billing for: {$studentNames}\n";
            $message .= "Amount: {$billing->amount} {$currencySymbol}\n";
            if ($billing->message) {
                $message .= "Message: {$billing->message}\n";
            }
            $message .= "Payment link: {$paymentLink}";

            $results = [];
            foreach ($students as $student) {
                if ($student->whatsapp_number) {
                    $result = $this->whatsAppService->sendMessage(
                        $student->whatsapp_number,
                        $message
                    );
                    $results[] = [
                        'student_id' => $student->id,
                        'student_name' => $student->name,
                        'success' => $result['success'],
                        'message' => $result['message'] ?? null,
                    ];
                }
            }

            return response()->json([
                'data' => $results,
                'message' => 'WhatsApp messages sent',
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending WhatsApp: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send WhatsApp messages'], 500);
        }
    }
}
