<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Services\AssignmentManagementService;
use App\Http\Requests\Admin\StoreAssignmentRequest;
use App\Http\Requests\Admin\UpdateAssignmentRequest;
use App\Models\ModuleContent;
use App\Models\ContentProgress;
use Illuminate\Http\Request;

class AssignmentManagementController extends Controller
{
    protected $assignmentManagementService;

    public function __construct(AssignmentManagementService $assignmentManagementService)
    {
        $this->assignmentManagementService = $assignmentManagementService;
    }

    /**
     * Display a listing of assignments
     */
    public function index()
    {
        $assignments = $this->assignmentManagementService->getAllAssignments();
        return response()->json([
            'success' => true,
            'data' => $assignments
        ]);
    }

    /**
     * Store a newly created assignment
     */
    public function store(StoreAssignmentRequest $request)
    {
        try {
            $assignment = $this->assignmentManagementService->createAssignment($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil ditambahkan',
                'data' => $assignment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan tugas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified assignment and its submissions.
     */
    public function show($id)
    {
        try {
            // Find the module content by its ID
            $moduleContent = ModuleContent::with('content')->findOrFail($id);

            // Ensure the content is an assignment
            if (!$moduleContent->content instanceof Assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Konten yang diberikan bukan tugas.'
                ], 400);
            }

            $assignment = $moduleContent->content;

            // Get all content progress for this assignment, including submission and user details
            $submissions = ContentProgress::with(['submission', 'enrollment.user'])
                ->where('module_content_id', $moduleContent->id)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'assignment' => $assignment,
                    'submissions' => $submissions,
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data tugas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified assignment
     */
    public function update(UpdateAssignmentRequest $request, $id)
    {
        try {
            $moduleContent = \App\Models\ModuleContent::findOrFail($id);
            $assignment = $moduleContent->content;

            if (!$assignment instanceof \App\Models\Assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Konten yang diberikan bukan tugas.'
                ], 400);
            }

            $assignment = $this->assignmentManagementService->updateAssignment($assignment, $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil diperbarui',
                'data' => $assignment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui tugas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified assignment
     */
    public function destroy(Assignment $assignment)
    {
        try {
            $this->assignmentManagementService->deleteAssignment($assignment);
            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tugas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}