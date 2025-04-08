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

class PaymentController extends Controller
{
    public function __construct()
    {
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

    public function createTransaction(PaymentsCreateTransactionRequest $request)
    {
        try {
            Log::info('Creating new transaction', ['user_id' => Auth::id()]);

            $order_id = uniqid();
            $user = Auth::user();
            $customerDetails = $this->prepareCustomerDetails($user);

            $registration = $this->updateRegistration($request->registration_id, $order_id);

            $params = [
                'transaction_details' => [
                    'order_id' => $order_id,
                    'gross_amount' => $request->amount,
                ],
                'customer_details' => $customerDetails
            ];

            $transaction = $this->processTransaction($params);

            Log::info('Transaction created successfully', [
                'order_id' => $order_id,
                'registration_id' => $registration->id
            ]);

            return response()->json([
                'url' => $transaction->redirect_url,
                'registration_id' => $registration->id,
                'token' => $transaction->token
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create transaction', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to create transaction',
                'message' => $e->getMessage()
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

    public function handleCallback(Request $request)
    {
        try {
            Log::info('Received payment callback', ['order_id' => $request->order_id]);

            if (!$this->isValidSignature($request)) {
                Log::warning('Invalid signature in callback', ['order_id' => $request->order_id]);
                return response()->json(['message' => 'Invalid signature'], 400);
            }

            $registration = $this->processCallbackUpdate($request);

            Log::info('Payment callback processed successfully', [
                'order_id' => $request->order_id,
                'status' => $request->transaction_status
            ]);

            return response()->json(['message' => 'Callback handled successfully']);
        } catch (\Exception $e) {
            Log::error('Payment callback processing failed', [
                'error' => $e->getMessage(),
                'order_id' => $request->order_id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['message' => 'Failed to process callback'], 500);
        }
    }

    private function isValidSignature(Request $request): bool
    {
        $serverKey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        return $hashed === $request->signature_key;
    }

    private function processCallbackUpdate(Request $request): Registration
    {
        $registration = Registration::where('midtrans_order_id', $request->order_id)->firstOrFail();

        $registration->update([
            'payment_status' => $request->transaction_status,
            'transaction_id' => $request->transaction_id,
            'payment_type' => $request->payment_type,
            'transaction_time' => $request->transaction_time,
            'gross_amount' => $request->gross_amount
        ]);

        return $registration;
    }

    public function updatePaymentStatus(Request $request)
    {
        try {
            Log::info('Updating payment status', ['order_id' => $request->order_id]);

            $registration = Registration::where('midtrans_order_id', $request->order_id)->firstOrFail();

            // Update registration status
            $registration->update([
                'payment_status' => $request->transaction_status,
                'transaction_id' => $request->transaction_id,
                'payment_type' => $request->payment_type,
                'transaction_time' => $request->transaction_time,
                'gross_amount' => $request->gross_amount,
                'fraud_status' => $request->fraud_status,
                'verification' => true
            ]);

            // Jika pembayaran settlement/success, update verification juga
            if ($request->transaction_status === 'settlement') {
                $registration->update([
                    'verification' => true
                ]);
            }

            Log::info('Payment status updated successfully', [
                'order_id' => $request->order_id,
                'status' => $request->transaction_status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully',
                'data' => $registration
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update payment status', [
                'error' => $e->getMessage(),
                'order_id' => $request->order_id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment status'
            ], 500);
        }
    }
}
