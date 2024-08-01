<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Registration;
use App\Models\User;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Set konfigurasi Midtrans secara manual
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');
    }

    public function createTransaction(Request $request)
    {
        // Validasi input
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $order_id = uniqid(); // Generate unique order ID

        // Dapatkan informasi pengguna
        $user = User::find($request->user_id);
        $name = explode(' ', $user->name);
        $first_name = $name[0];
        $last_name = count($name) > 1 ? implode(' ', array_slice($name, 1)) : '';
        // Buat entry registrasi sementara
        $registration = Registration::findOrFail($request->registration_id);
        $registration->update([
            'payment_status' => 'pending',
            'midtrans_order_id' => $order_id,
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => $request->amount,
            ],
            'customer_details' => [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $request->email,
                'phone' => $request->phone,
            ],
        ];

        try {
            $paymentUrl = Snap::createTransaction($params)->redirect_url;
            return response()->json(['url' => $paymentUrl, 'registration_id' => $registration->id]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function handleCallback(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashed == $request->signature_key) {
            $registration = Registration::where('midtrans_order_id', $request->order_id)->first();

            if ($registration) {
                $registration->payment_status = $request->transaction_status;
                $registration->transaction_id = $request->transaction_id;
                $registration->payment_type = $request->payment_type;
                $registration->save();

                return response()->json(['message' => 'Callback handled successfully']);
            }
        }

        return response()->json(['message' => 'Invalid signature'], 400);
    }
}
