<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Snap;

class PaymentService
{
    protected $enrollmentService;

    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }

    public function createTransaction(Registration $registration, float $amount, array $customerDetails)
    {
        try {
            DB::beginTransaction();

            $orderId = uniqid();

            // Create payment record
            $payment = Payment::create([
                'registration_id' => $registration->id,
                'midtrans_order_id' => $orderId,
                'gross_amount' => $amount,
                'transaction_status' => 'pending'
            ]);

            // Prepare Midtrans parameters
            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $amount,
                ],
                'customer_details' => $customerDetails,
                // callbacks dev local
                'callbacks' => [
                    'finish' => config('services.midtrans.callback_url'),
                    'notification' => config('services.midtrans.callback_url')
                ]
            ];

            // Get Snap Token
            $snapToken = Snap::getSnapToken($params);
            $snapUrl = $this->getSnapUrl($snapToken);

            // Update payment with snap data
            $payment->update([
                'snap_token' => $snapToken,
                'payment_url' => $snapUrl
            ]);

            DB::commit();

            return $payment;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment creation failed', [
                'error' => $e->getMessage(),
                'registration_id' => $registration->id
            ]);
            throw $e;
        }
    }

    public function handleCallback(array $callbackData)
    {
        try {
            DB::beginTransaction();

            $payment = Payment::where('midtrans_order_id', $callbackData['order_id'])->firstOrFail();

            $payment->update([
                'transaction_status' => $callbackData['transaction_status'],
                'transaction_id' => $callbackData['transaction_id'],
                'payment_type' => $callbackData['payment_type'],
                'transaction_time' => $callbackData['transaction_time'],
                'gross_amount' => $callbackData['gross_amount'],
                'fraud_status' => $callbackData['fraud_status'],
                'payment_details' => $callbackData
            ]);

            // If payment is successful, create enrollment
            if ($callbackData['transaction_status'] === 'settlement') {
                $this->handlePaymentSuccess($payment);
            }


            DB::commit();
            return $payment;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment callback handling failed', [
                'error' => $e->getMessage(),
                'callback_data' => $callbackData
            ]);
            throw $e;
        }
    }

    private function getSnapUrl(string $token): string
    {
        return config('services.midtrans.is_production')
            ? "https://app.midtrans.com/snap/v2/vtweb/{$token}"
            : "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$token}";
    }

    public function handlePaymentSuccess(Payment $payment)
    {
        try {
            DB::beginTransaction();

            // Update registration status
            $payment->registration->update([
                'verification' => true,
                'verified_at' => now(),
                'status' => 'active'
            ]);

            // Create enrollment
            $this->enrollmentService->enrollUserAfterPayment($payment->registration);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to handle payment success', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id
            ]);
            throw $e;
        }
    }
}
