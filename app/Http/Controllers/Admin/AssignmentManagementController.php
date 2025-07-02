<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Services\AssignmentManagementService;
use App\Http\Requests\Admin\StoreAssignmentRequest;
use App\Http\Requests\Admin\UpdateAssignmentRequest;
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
     * Display the specified assignment
     */
    public function show($id)
    {
        try {
            $assignment = $this->assignmentManagementService->getAssignmentById($id);
            return response()->json([
                'success' => true,
                'data' => $assignment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified assignment
     */
    public function update(UpdateAssignmentRequest $request, Assignment $assignment)
    {
        try {
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