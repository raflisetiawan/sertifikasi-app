<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserDashboardResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load([
            'enrollments' => function ($query) {
                $query->with([
                    'course:id,name,description,operational_start,status,image,place',
                    'moduleProgresses' => function ($q) {
                        $q->orderBy('created_at', 'desc');
                    }
                ])
                ->orderBy('created_at', 'desc');
            },
            'registrations' => function ($query) {
                $query->with([
                    'course:id,name,price',
                    'payment' => function ($q) {
                        $q->select('id', 'registration_id', 'transaction_status', 'payment_type', 'transaction_time', 'gross_amount');
                    }
                ])
                ->orderBy('created_at', 'desc');
            }
        ]);

        return new UserDashboardResource($user);
    }

    /**
     * Display a listing of certificates for the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCertificates(Request $request)
    {
        $user = Auth::user();

        $certificates = $user->enrollments()
            ->whereNotNull('certificate_path')
            ->with('course:id,name,description,image') // Load course details
            ->latest('completed_at')
            ->paginate(10);

        $certificates->getCollection()->transform(function ($enrollment) {
            $enrollment->certificate_url = Storage::disk('public')->url($enrollment->certificate_path);
            return $enrollment;
        });

        return response()->json($certificates);
    }
}
