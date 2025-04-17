<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['registration.course:id,name'])
            ->whereHas('registration', function($q) {
                $q->where('user_id', auth()->id());
            });

        // Add sorting
        if ($request->has('sort')) {
            $query->orderBy('created_at', $request->sort === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $payments = $query->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $payments->items() ? collect($payments->items())->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'course_name' => $payment->registration->course->name,
                    'course_id' => $payment->registration->course->id,
                    'order_id' => $payment->midtrans_order_id,
                    'amount' => $payment->gross_amount,
                    'status' => $payment->transaction_status,
                    'payment_type' => $payment->payment_type,
                    'transaction_time' => $payment->transaction_time,
                    'created_at' => $payment->created_at
                ];
            }) : [],
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total()
            ]
        ]);
    }
}
