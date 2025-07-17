<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::query();

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Filter by category (using key_concepts as a proxy for now)
        if ($request->has('category')) {
            $category = $request->input('category');
            $query->whereJsonContains('key_concepts', $category);
        }

        // Sorting
        if ($request->has('sort_by')) {
            $sortBy = $request->input('sort_by');
            $sortOrder = $request->input('sort_order', 'asc'); // Default to ascending

            switch ($sortBy) {
                case 'popular':
                    // Assuming 'popular' is based on the number of enrollments
                    // This would require a relationship and a count, for now, let's sort by created_at desc
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'latest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'price':
                    $query->orderBy('price', $sortOrder);
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        }

        $courses = $query->with('trainers')->paginate(10); // Paginate with 10 items per page

        return response()->json($courses);
    }

    public function show(Course $course)
    {
        return response()->json($course->load('trainers'));
    }
}
