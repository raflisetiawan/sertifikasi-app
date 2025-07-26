<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use App\Models\ContentProgress;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\ModuleProgress;
use App\Models\Registration;
use App\Models\User;
use App\Notifications\RegistrationApproved;
use App\Notifications\RegistrationNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Midtrans\Snap;
use Midtrans\Config as MidtransConfig;

class RegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Ambil query parameters jika ada
        $courseId = $request->input('course_id');
        $date = $request->input('date');
        $verification = $request->input('verification'); // Filter default ke belum terverifikasi
        $userEmail = $request->input('user_email'); // Email pengguna

        // Query untuk mendapatkan pendaftar kelas
        $query = Registration::with(['user:id,email', 'course:id,name']);

        // Filter berdasarkan course_id jika disertakan
        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        // Filter berdasarkan tanggal jika disertakan
        if ($date) {
            $query->whereDate('created_at', $date);
        }

        // Filter berdasarkan verifikasi
        if ($verification !== null) {
            // Cast $verification to boolean explicitly
            $verification = filter_var($verification, FILTER_VALIDATE_BOOLEAN);
            $query->where('verification', $verification);
        }

        // Filter berdasarkan email pengguna
        if ($userEmail) {
            $query->whereHas('user', function ($q) use ($userEmail) {
                $q->where('email', 'like', "%$userEmail%");
            });
        }

        // Ambil data pendaftar kelas
        $registrations = $query->get(['id', 'user_id', 'course_id', 'verification']);

        // Ubah nilai verifikasi dari angka (0/1) menjadi boolean (true/false)
        $registrations->transform(function ($registration) {
            $registration->verification = (bool) $registration->verification;
            return $registration;
        });

        return response()->json(['data' => $registrations], 200);
    }

     /**
     * Store a newly created registration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $userId = auth()->id();
        $courseId = $request->course_id;
        $user = auth()->user();
        $course = Course::findOrFail($courseId);

        // Check for existing registration
        $existingRegistration = Registration::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if ($existingRegistration) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah mendaftar kelas ini'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // IF COURSE IS FREE
            if ($course->price <= 0) {
                $registration = Registration::create([
                    'user_id' => $userId,
                    'course_id' => $courseId,
                    'status' => 'completed', // Langsung complete karena gratis
                    'verification' => true,
                    'verified_at' => now(),
                    'payment_status' => 'paid' // Anggap sudah lunas
                ]);

                $enrollment = Enrollment::create([
                    'user_id' => $registration->user_id,
                    'course_id' => $registration->course_id,
                    'registration_id' => $registration->id,
                    'status' => 'active',
                    'started_at' => now(),
                    'progress_percentage' => 0.0
                ]);

                $this->initializeModuleProgress($enrollment);
                $this->logEnrollmentCreation($enrollment);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Pendaftaran berhasil, Anda langsung terdaftar di kelas gratis ini.',
                    'data' => [
                        'registration' => $registration->load('course'),
                        'enrollment' => $enrollment->load('course')
                    ]
                ], 201);
            }

            // IF COURSE IS PAID (existing logic)
            $registration = Registration::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'status' => 'pending'
            ]);

            // Send email notification to admin
            $adminEmail = config('mail.from.address');
            Notification::route('mail', $adminEmail)
                ->notify(new RegistrationNotification($registration));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pendaftaran berhasil dibuat',
                'data' => [
                    'registration' => $registration->load('course'),
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pendaftaran. Silakan coba lagi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the courses registered by a specific user.
     *
     * @param  int  $userId
     * @return \Illuminate\Http\Response
     */
    public function getUserCourses()
    {
        $userId = auth()->id();

    $registrations = Registration::with('course:id,name,image,place,operational_start,status,guidelines')
        ->where('user_id', $userId)
        ->get(['course_id', 'verification']);

    if ($registrations->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'Anda belum mendaftar kelas'
        ], 404);
    }

        // Extract the course IDs from registrations
        $courseIds = $registrations->pluck('course_id');

        // Retrieve the courses based on the course IDs
        $courses = Course::WhereIn('id', $courseIds)
            ->get(['id', 'name', 'operational_start', 'image', 'place', 'status', 'guidelines']);

        // Append verification status to each course
        $courses->each(function ($course) use ($registrations) {
            $registration = $registrations->where('course_id', $course->id)->first();
            $registrations->transform(function ($registration) {
                $registration->verification = (bool) $registration->verification;
                return $registration;
            });
            $course->verification = $registration->verification;
            $course->image = asset('/storage/courses/' . $course->image);
            $course->guidelines = asset('/storage/courses/guideline/' . $course->guidelines);
        });

        return response()->json(['data' => $courses], Response::HTTP_OK);
    }
    /**
     * Approve a registration and create enrollment.
     *
     * @param  int  $registrationId
     * @return \Illuminate\Http\Response
     */
    public function approveRegistration($registrationId)
    {
        try {
            return DB::transaction(function () use ($registrationId) {
                // Find the registration with relationships
                $registration = Registration::with(['user', 'course'])
                    ->findOrFail($registrationId);

                // Validate registration state
                if ($registration->verification) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pendaftaran sudah diverifikasi sebelumnya'
                    ], 422);
                }

                if (!$registration->payment_status === 'paid') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pembayaran belum selesai'
                    ], 422);
                }

                // Check for existing enrollment
                $existingEnrollment = Enrollment::where([
                    'user_id' => $registration->user_id,
                    'course_id' => $registration->course_id
                ])->first();

                if ($existingEnrollment) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pengguna sudah terdaftar di kelas ini'
                    ], 422);
                }

                // Check if course is still active
                if ($registration->course->status !== 'active') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kelas tidak aktif'
                    ], 422);
                }

                // Update registration verification status
                $registration->update([
                    'verification' => true,
                    'verified_at' => now(),
                    'verified_by' => auth()->id()
                ]);

                // Create new enrollment
                $enrollment = Enrollment::create([
                    'user_id' => $registration->user_id,
                    'course_id' => $registration->course_id,
                    'registration_id' => $registration->id,
                    'status' => 'active',
                    'started_at' => now(),
                    'progress_percentage' => 0.0
                ]);

                // Initialize module progress
                $this->initializeModuleProgress($enrollment);

                // Log the enrollment creation
                $this->logEnrollmentCreation($enrollment);

                // Send notification to user
                try {
                    Notification::route('mail', $registration->user->email)
                        ->notify(new RegistrationApproved($registration));
                } catch (\Exception $e) {
                    // Log notification failure but don't rollback transaction
                    Log::error('Failed to send enrollment notification: ' . $e->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Pendaftaran berhasil diverifikasi dan enrollment telah dibuat',
                    'data' => [
                        'registration' => $registration->fresh(['user', 'course']),
                        'enrollment' => $enrollment->load('course'),
                        'module_progress' => ModuleProgress::where('enrollment_id', $enrollment->id)
                            ->with('module')
                            ->get()
                    ]
                ], 201);
            });
        } catch (\Exception $e) {
            Log::error('Registration approval failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memverifikasi pendaftaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initialize module progress for new enrollment.
     *
     * @param  Enrollment  $enrollment
     * @return void
     */
    private function initializeModuleProgress(Enrollment $enrollment)
    {
        // Get all modules with their contents
        $modules = $enrollment->course->modules()
            ->orderBy('order')
            ->with(['contents' => function ($query) {
                $query->orderBy('order');
            }])
            ->get();

        $now = now();

        foreach ($modules as $index => $module) {
            // Create module progress record
            $moduleProgress = ModuleProgress::create([
                'enrollment_id' => $enrollment->id,
                'module_id' => $module->id,
                'status' => $index === 0 ? 'active' : 'locked',
                'progress_percentage' => 0.0,
                'started_at' => $index === 0 ? $now : null
            ]);

            // Create content progress records
            foreach ($module->contents as $content) {
                ContentProgress::create([
                    'enrollment_id' => $enrollment->id,
                    'module_content_id' => $content->id,
                    'status' => 'not_started',
                    'score' => null,
                    'attempts' => 0,
                    'last_attempt_at' => null
                ]);
            }
        }
    }

    /**
     * Log enrollment creation to audit trail.
     *
     * @param  Enrollment  $enrollment
     * @return void
     */
    private function logEnrollmentCreation(Enrollment $enrollment)
    {
        AuditTrail::create([
            'user_id' => auth()->id() ?? 1,
            'action' => 'enrollment_created',
            'model_type' => 'enrollment',
            'model_id' => $enrollment->id,
            'description' => sprintf(
                'Created enrollment for user %s in course "%s"',
                $enrollment->user->email ?? $enrollment->user_id,
                $enrollment->course->name ?? $enrollment->course_id
            ),
            'old_values' => null,
            'new_values' => [
                'enrollment' => $enrollment->toArray(),
                'created_at' => now()->toDateTimeString(),
                'created_by' => auth()->user()->email ?? 'system'
            ]
        ]);
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
            'payment_proof_url' => $paymentProof,
            'verification' => $registration->verification
        ], 200);
    }
}
