<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $materials = Material::all();

        return response()->json(['data' => $materials], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $material = Material::find($id);

        if (!$material) {
            return response()->json(['message' => 'Material not found'], 404);
        }

        return response()->json(['data' => $material], 200);
    }

    // You can add other methods like store, update, and destroy as needed.

    // For example:

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'file' => 'required|file',
            'course_id' => 'required|exists:courses,id',
        ]);


        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $file = $request->file('file');
        if ($file) {
            $file->storeAs('public/courses/materials', $file->hashName());
        }

        $material = Material::create([
            'title' => $request->title,
            'file' => $file->hashName(),
            'description' => $request->description,
            'course_id' => $request->course_id,
        ]);

        return response()->json(['data' => $material, 'message' => 'Material created successfully'], 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'file' => 'file',
            'course_id' => 'required|exists:courses,id',
        ]);


        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $material = Material::find($id);

        if (!$material) {
            return response()->json(['message' => 'Material not found'], 404);
        }

        $file = $request->file('file');
        if ($file) {
            $file->storeAs('public/courses/materials', $file->hashName());
            Storage::delete('public/courses/materials/' . basename($material->file));
            $material->update([
                'title' => $request->title,
                'file' => $file ? $file->hashName() : $material->file,
                'description' => $request->description,
                'course_id' => $request->course_id,
            ]);
        } else {
            $material->update([
                'title' => $request->title,
                'description' => $request->description,
                'course_id' => $request->course_id,
            ]);
        }
        return response()->json(['data' => $material, 'message' => 'Material updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $material = Material::find($id);

        if (!$material) {
            return response()->json(['message' => 'Material not found'], 404);
        }

        Storage::delete('public/courses/materials/' . basename($material->file));
        $material->delete();
        return response()->json(['message' => 'Material deleted successfully'], 200);
    }

    /**
     * Get materials based on the given course ID.
     *
     * @param  int  $courseId
     * @return \Illuminate\Http\Response
     */
    public function getMaterialsByCourse($courseId)
    {
        $materials = Material::where('course_id', $courseId)->get();

        return response()->json(['data' => $materials], 200);
    }
}
