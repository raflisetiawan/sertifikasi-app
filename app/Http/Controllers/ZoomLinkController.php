<?php

namespace App\Http\Controllers;

use App\Models\CourseZoomLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ZoomLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $zoomLinks = CourseZoomLink::all();

        return response()->json(['data' => $zoomLinks], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'link' => 'required|url',
            'course_id' => 'required|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $zoomLink = CourseZoomLink::create([
            'link' => $request->link,
            'course_id' => $request->course_id,
        ]);

        return response()->json(['data' => $zoomLink], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $zoomLink = CourseZoomLink::find($id);

        if (!$zoomLink) {
            return response()->json(['message' => 'Data Zoom Link tidak ditemukan'], 404);
        }

        return response()->json(['data' => $zoomLink], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'link' => 'required|url',
            'course_id' => 'required|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $zoomLink = CourseZoomLink::find($id);

        if (!$zoomLink) {
            return response()->json(['message' => 'Data Zoom Link tidak ditemukan'], 404);
        }

        $zoomLink->update([
            'link' => $request->link,
            'course_id' => $request->course_id,
        ]);

        return response()->json(['data' => $zoomLink], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $zoomLink = CourseZoomLink::find($id);

        if (!$zoomLink) {
            return response()->json(['message' => 'Data Zoom Link tidak ditemukan'], 404);
        }

        $zoomLink->delete();

        return response()->json(['message' => 'Data Zoom Link berhasil dihapus'], 200);
    }
}
