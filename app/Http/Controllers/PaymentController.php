<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Payments\CreateTransactionRequest as PaymentsCreateTransactionRequest;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        $this->initializeMidtransConfig();
    }

    private function initializeMidtransConfig(): void
    {
        try {
            Config::$serverKey = config('services.midtrans.server_key');
            Config::$isProduction = config('services.midtrans.is_production');
            Config::$isSanitized = config('services.midtrans.is_sanitized');
            Config::$is3ds = config('services.midtrans.is_3ds');

            Log::info('Midtrans configuration initialized successfully');
        } catch (\Exception $e) {
            Log::error('Failed to initialize Midtrans configuration', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function createTransaction(Request $request)
    {
        $request->validate([
            'registration_id' => 'required|exists:registrations,id',
            'amount' => 'required|numeric|min:1'
        ]);

        try {
            $registration = Registration::with('user')->findOrFail($request->registration_id);

            $customerDetails = $this->prepareCustomerDetails($registration->user);

            $payment = $this->paymentService->createTransaction(
                $registration,
                $request->amount,
                $customerDetails
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'data' => [
                    'payment_url' => $payment->payment_url,
                    'snap_token' => $payment->snap_token
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Payment creation failed', [
                'error' => $e->getMessage(),
                'registration_id' => $request->registration_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment'
            ], 500);
        }
    }

     public function handleCallback(Request $request)
    {
        Log::debug($request->all());
        try {
            if (!$this->isValidSignature($request)) {
                return response()->json([
                    'message' => 'Invalid signature'
                ], 400);
            }

            $payment = $this->paymentService->handleCallback($request->all());
            Log::info('Payment callback processed successfully', [
                'order_id' => $request->order_id,
                'status' => $payment->transaction_status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment callback processed successfully',
                'data' => [
                    'payment' => $payment->fresh(['registration']),
                    'enrollment' => $payment->registration->enrollment
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Payment callback failed', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment callback',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    private function prepareCustomerDetails($user): array
    {
        $name = explode(' ', $user->name);
        return [
            'first_name' => $name[0],
            'last_name' => count($name) > 1 ? implode(' ', array_slice($name, 1)) : '',
            'email' => $user->email,
            'phone' => $user->phone ?? ''
        ];
    }

    private function updateRegistration(int $registrationId, string $orderId): Registration
    {
        $registration = Registration::findOrFail($registrationId);
        $registration->update([
            'payment_status' => 'pending',
            'midtrans_order_id' => $orderId,
        ]);
        return $registration;
    }

    private function processTransaction(array $params)
    {
        try {
            $snapTransaction = Snap::createTransaction($params);
            $snapTransaction->token = Snap::getSnapToken($params);
            return $snapTransaction;
        } catch (\Exception $e) {
            Log::error('Midtrans transaction processing failed', [
                'error' => $e->getMessage(),
                'params' => $params
            ]);
            throw $e;
        }
    }

    private function isValidSignature(Request $request): bool
    {
        $serverKey = config('services.midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        return $hashed === $request->signature_key;
    }
public function updatePaymentStatus(Request $request)
{
    try {
        Log::info('Updating payment status', ['order_id' => $request->order_id]);

        // Find payment by midtrans_order_id instead of registration
        $payment = Payment::where('midtrans_order_id', $request->order_id)->firstOrFail();

        DB::beginTransaction();

        // Update payment status
        $payment->update([
            'transaction_status' => $request->transaction_status,
            'transaction_id' => $request->transaction_id,
            'payment_type' => $request->payment_type,
            'transaction_time' => $request->transaction_time,
            'gross_amount' => $request->gross_amount,
            'fraud_status' => $request->fraud_status,
            'payment_details' => $request->all()
        ]);

        // If payment is successful, update registration verification
        if ($request->transaction_status === 'settlement') {
            $payment->registration->update([
                'verification' => true,
                'verified_at' => now(),
                'status' => 'active'
            ]);
        }

        DB::commit();

        Log::info('Payment status updated successfully', [
            'order_id' => $request->order_id,
            'status' => $request->transaction_status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully',
            'data' => [
                'payment' => $payment->fresh(),
                'registration' => $payment->registration->fresh()
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to update payment status', [
            'error' => $e->getMessage(),
            'order_id' => $request->order_id ?? null
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to update payment status',
            'error' => $e->getMessage()
        ], 500);
    }
}

private function processCallbackUpdate(Request $request): Payment
{
    $payment = Payment::where('midtrans_order_id', $request->order_id)->firstOrFail();

    DB::transaction(function () use ($payment, $request) {
        $payment->update([
            'transaction_status' => $request->transaction_status,
            'transaction_id' => $request->transaction_id,
            'payment_type' => $request->payment_type,
            'transaction_time' => $request->transaction_time,
            'gross_amount' => $request->gross_amount,
            'fraud_status' => $request->fraud_status,
            'payment_details' => $request->all()
        ]);

        if ($request->transaction_status === 'settlement') {
            $payment->registration->update([
                'verification' => true,
                'verified_at' => now(),
                'status' => 'active'
            ]);
        }
    });

    return $payment;
}
}
