<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ModuleContentManagementController extends Controller
{
    /**
     * Display a listing of module contents
     */
    public function index(Module $module)
    {
        $contents = $module->contents()
            ->with('content')
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $contents
        ]);
    }

    /**
     * Store a newly created module content
     */
    public function store(Request $request, Module $module)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content_type' => 'required|in:video,text,quiz,assignment,file',
            'content_id' => 'required|integer',
            'order' => 'required|integer|min:0',
            'is_required' => 'boolean',
            'minimum_duration_seconds' => 'nullable|integer|min:0',
            'completion_rules' => 'nullable|json'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Validate unique order
            $orderExists = $module->contents()->where('order', $request->order)->exists();
            if ($orderExists) {
                return response()->json([
                    'success' => false,
                    'errors' => ['order' => ['The order has already been taken for this module.']]
                ], 422);
            }

            $content = $module->contents()->create($validator->validated());

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $content->load('content')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create module content',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified module content
     */
    public function show(Module $module, ModuleContent $content)
    {
        if ($content->module_id !== $module->id) {
            return response()->json([
                'success' => false,
                'message' => 'Content does not belong to this module'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $content->load('content')
        ]);
    }

    /**
     * Update the specified module content
     */
    public function update(Request $request, Module $module, ModuleContent $content)
    {
        if ($content->module_id !== $module->id) {
            return response()->json([
                'success' => false,
                'message' => 'Content does not belong to this module'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'order' => 'integer|min:0',
            'is_required' => 'boolean',
            'minimum_duration_seconds' => 'nullable|integer|min:0',
            'completion_rules' => 'nullable|json'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Validate unique order on update
            if ($request->has('order') && $request->order !== $content->order) {
                $orderExists = $module->contents()
                    ->where('id', '!=', $content->id)
                    ->where('order', $request->order)
                    ->exists();

                if ($orderExists) {
                    return response()->json([
                        'success' => false,
                        'errors' => ['order' => ['The order has already been taken for this module.']]
                    ], 422);
                }
            }

            $content->update($validator->validated());

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $content->load('content')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update module content',
                'error' => $e->getMessage()
            ], 500);
        }
    }

/**
 * Remove the specified module content and its related content
 */
public function destroy(Module $module, ModuleContent $content)
{
    if ($content->module_id !== $module->id) {
        return response()->json([
            'success' => false,
            'message' => 'Content does not belong to this module'
        ], 404);
    }

    try {
        DB::beginTransaction();

        // Delete the related content based on content_type
        switch ($content->content_type) {
            case 'text':
                if ($content->content) {
                    $content->content()->delete();
                }
                break;

            case 'quiz':
                if ($content->content) {
                    // Delete quiz and all related data
                    $content->content()->delete();
                }
                break;

            case 'assignment':
                if ($content->content) {
                    $content->content()->delete();
                }
                break;

            case 'file':
                if ($content->content) {
                    // Delete the physical file first
                    if ($content->content->file_path) {
                        Storage::delete('public/' . $content->content->file_path);
                    }
                    $content->content()->delete();
                }
                break;

            case 'video':
                if ($content->content) {
                    // Delete video related data
                    $content->content()->delete();
                }
                break;
        }

        // Reorder remaining contents
        $module->contents()
            ->where('order', '>', $content->order)
            ->decrement('order');

        // Finally delete the module content entry
        $content->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Module content and related data deleted successfully'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Failed to delete module content',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Reorder multiple contents at once
     */
    public function reorder(Request $request, Module $module)
    {
        $validator = Validator::make($request->all(), [
            'contents' => 'required|array',
            'contents.*.id' => 'required|exists:module_contents,id',
            'contents.*.order' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($request->contents as $item) {
                $content = ModuleContent::find($item['id']);
                if ($content->module_id === $module->id) {
                    $content->update(['order' => $item['order']]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $module->contents()->orderBy('order')->get()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder contents',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}