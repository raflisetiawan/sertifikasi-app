<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Registration;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Card Statistics
        $totalUsers = User::count();
        $totalActiveCourses = Course::where('status', 'active')->count();
        $newRegistrationsLastMonth = Registration::where('created_at', '>=', Carbon::now()->subMonth())->count();
        $totalRevenue = Payment::where('transaction_status', 'completed')->sum('gross_amount'); // Assuming 'completed' status and 'gross_amount' column

        // User Registration Trend (last 7 days)
        $registrationTrends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = User::whereDate('created_at', $date)->count();
            $registrationTrends[] = [
                'date' => $date->format('Y-m-d'),
                'count' => $count,
            ];
        }

        // Recently Updated Courses
        $recentlyUpdatedCourses = Course::orderBy('updated_at', 'desc')->take(5)->get();

        // Recently Registered Users
        $recentlyRegisteredUsers = User::orderBy('created_at', 'desc')->take(5)->get();

        return response()->json([
            'statistics' => [
                'total_users' => $totalUsers,
                'total_active_courses' => $totalActiveCourses,
                'new_registrations_last_month' => $newRegistrationsLastMonth,
                'total_revenue' => $totalRevenue,
            ],
            'trends' => [
                'registration_trends' => $registrationTrends,
            ],
            'recent_activities' => [
                'recently_updated_courses' => $recentlyUpdatedCourses,
                'recently_registered_users' => $recentlyRegisteredUsers,
            ],
        ]);
    }
}
