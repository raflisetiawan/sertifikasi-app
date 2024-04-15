<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Registration;
use App\Models\User;
use App\Notifications\RegistrationApproved;
use App\Notifications\RegistrationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class RegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Mengambil semua pendaftar kelas beserta email, nama kelas, ID registrasi, dan verifikasi yang terkait
        $registrations = Registration::with(['user:id,email', 'course:id,name'])->get(['id', 'user_id', 'course_id', 'verification']);

        // Mengonversi nilai verifikasi dari angka (0/1) menjadi boolean (true/false)
        $registrations->transform(function ($registration) {
            $registration->verification = (bool) $registration->verification;
            return $registration;
        });

        return response()->json(['data' => $registrations], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $errorMessagesOfPaymentProof = [
            'payment_proof.required' => 'Silakan unggah bukti pembayaran.',
            'payment_proof.image' => 'Bukti pembayaran harus berupa file gambar.',
            'payment_proof.max' => 'Ukuran bukti pembayaran tidak boleh melebihi 10 MB.'
        ];

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'payment_proof' => 'required|image|max:10000'
        ], $errorMessagesOfPaymentProof);

        $userId = $request->user_id;
        $courseId = $request->course_id;

        $existingRegistration = Registration::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if ($existingRegistration) {
            // User is already registered for this course
            return response()->json(['message' => 'Anda sudah mendaftar kelas ini'], 422);
        }

        try {
            // Use transactions to ensure atomicity of database operations
            DB::beginTransaction();

            $payment_proof_image = $request->file('payment_proof');
            $payment_proof_image->storeAs('public/payment_proof_images', $payment_proof_image->hashName());

            // Check if the user is already registered for the course
            $registration = Registration::create([
                'user_id' => $request->user_id,
                'course_id' => $request->course_id,
                'payment_proof' => $request->payment_proof->hashName(),
            ]);

            // Commit the transaction if all operations are successful
            DB::commit();

            // Send email notification to admin
            $adminEmail = Config::get('mail.from.address');
            Notification::route('mail', $adminEmail)->notify(new RegistrationNotification($registration));

            return response()->json(['data' => $registration, 'message' => 'Registration created successfully'], 201);
        } catch (\Exception $e) {
            // Rollback the transaction if an error occurs
            DB::rollBack();
            // You can log the error here or return an error response
            return response()->json(['message' => 'Failed to create registration. Please try again later.'], 500);
        }
    }

    /**
     * Get the courses registered by a specific user.
     *
     * @param  int  $userId
     * @return \Illuminate\Http\Response
     */
    public function getUserCourses($userId)
    {
        // Retrieve the registrations for the given user ID
        $registrations = Registration::where('user_id', $userId)->get();

        if ($registrations->isEmpty()) {
            return response()->json(['message' => 'Anda belum mendaftar kelas'], 404);
        }

        // Extract the course IDs from registrations
        $courseIds = $registrations->pluck('course_id');

        // Retrieve the courses based on the course IDs
        $courses = Course::whereIn('id', $courseIds)->get();

        return response()->json(['data' => $courses], 200);
    }

    /**
     * Approve a registration.
     *
     * @param  int  $registrationId
     * @return \Illuminate\Http\Response
     */
    public function approveRegistration($registrationId)
    {
        // Find the registration by ID
        $registration = Registration::findOrFail($registrationId);

        try {
            // Use transactions to ensure atomicity of database operations
            DB::beginTransaction();

            // Commit the transaction if all operations are successful
            DB::commit();

            Notification::route('mail', $registration->user->email)->notify(new RegistrationApproved($registration));

            $registration->verification = true;
            $registration->save();

            return response()->json(['data' => $registration, 'message' => 'Registration approved successfully'], 201);
        } catch (\Exception $e) {
            // Rollback the transaction if an error occurs
            DB::rollBack();
            // You can log the error here or return an error response
            return response()->json(['message' => 'Failed to approve registration. Please try again later.'], 500);
        }


        // Set the verification status to true

        // You can add additional logic here, such as sending a notification to the user

        return response()->json(['message' => 'Registration approved successfully'], 200);
    }

      /**
     * Get detailed information about a registration.
     *
     * @param  int  $registrationId
     * @return \Illuminate\Http\Response
     */
    public function detailRegistration($registrationId)
    {
        // Temukan registrasi berdasarkan ID
        $registration = Registration::with(['user:id,email,name,phone_number', 'course:id,name,price'])->find($registrationId);

        if (!$registration) {
            return response()->json(['message' => 'Registration not found'], 404);
        }
        error_log($registration->payment_proof);

        // Dapatkan detail pengguna, kelas, dan harga kelas
        $userEmail = $registration->user->email;
        $courseName = $registration->course->name;
        $coursePrice = $registration->course->price;

        // Dapatkan tanggal mendaftar
        $registrationDate = $registration->created_at->toDateString();

        // Dapatkan nama bukti pembayaran
        $paymentProof = $registration->payment_proof;

        return response()->json([
            'email' => $userEmail,
            'name' => $registration->user->name,
            'phone_number' => $registration->user->phone_number,
            'registration_date' => $registrationDate,
            'course_name' => $courseName,
            'course_price' => $coursePrice,
            'payment_proof_url' => $paymentProof
        ], 200);
    }
}
