<?php

namespace App\Http\Controllers;

use App\Models\Trainer;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class TrainerController extends Controller
{
    /**
     * Display a listing of the trainers.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $trainers = Trainer::orderBy('created_at', 'desc')->get();

        // Convert numeric 'starred' values to boolean
        $trainers->transform(function ($trainer) {
            $trainer->starred = boolval($trainer->starred);
            return $trainer;
        });

        return response()->json($trainers);
    }

    /**
     * Display the specified trainer.
     *
     * @param  \App\Models\Trainer  $trainer
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Trainer $trainer)
    {
        return response()->json($trainer);
    }

    /**
     * Store a newly created trainer in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email|unique:trainers,email',
            'password' => 'required|string|min:8',
            'qualification' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $trainerRoleId = Role::where('name', 'trainer')->first()->id;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $trainerRoleId,
            'email_verified_at' => now(),
        ]);

        $trainer = Trainer::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'email' => $request->email,
            'qualification' => $request->qualification,
            'description' => $request->description,
            'starred' => $request->starred ?? false,
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('trainers/images', 'public');
            $trainer->update(['image' => $imagePath]);
        }

        return response()->json($trainer, 201);
    }

    /**
     * Update the specified trainer in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Trainer  $trainer
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Trainer $trainer)
    {
        $emailValidation = ['required', 'email', Rule::unique('trainers')->ignore($trainer->id)];

        if ($trainer->user) {
            $emailValidation[] = Rule::unique('users')->ignore($trainer->user->id);
        }

        $request->validate([
            'name' => 'required|string',
            'email' => $emailValidation,
            'qualification' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Update associated User record
        if ($trainer->user) {
            $trainer->user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);
        }

        $trainer->update($request->except(['email', 'user_id'])); // Exclude email and user_id from trainer update

        // Upload and store the image if provided
        if ($request->hasFile('image')) {
            if ($trainer->image) {
                Storage::disk('public')->delete($trainer->image);
            }

            $imagePath = $request->file('image')->store('trainers/images', 'public');
            $trainer->update(['image' => $imagePath]);
        }

        return response()->json($trainer, 200);
    }

    /**
     * Remove the specified trainer from storage.
     *
     * @param  \App\Models\Trainer  $trainer
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Trainer $trainer)
    {
        // Delete the trainer's image if it exists
        if ($trainer->image) {
            Storage::disk('public')->delete($trainer->image);
        }

        // Delete the associated User record
        if ($trainer->user) {
            $trainer->user->delete();
        }

        $trainer->delete();

        return response()->json(null, 204);
    }

    /**
     * Fetch trainers with the same qualification (limited to 4).
     * If not found, retrieve a random set of trainers.
     *
     * @param  string  $qualification
     * @return \Illuminate\Http\JsonResponse
     */
    public function trainersWithSameQualification($qualification, $id)
    {
        $trainers = Trainer::where('qualification', $qualification)
            ->where('id', '!=', $id)
            ->limit(4)
            ->get();

        // If no trainers are found with the specified qualification (excluding the specified id),
        // retrieve a random set of trainers
        if ($trainers->isEmpty()) {
            $trainers = Trainer::where('id', '!=', $id)->inRandomOrder()->limit(4)->get();
        }

        return response()->json($trainers);
    }

    public function toggleStarred(Trainer $trainer)
    {
        $trainer->update(['starred' => !$trainer->starred]);

        return response()->json(['message' => 'Starred status toggled successfully', 'starred' => $trainer->starred]);
    }

    /**
     * Get all starred trainers.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function starredTrainers()
    {
        $starredTrainers = Trainer::where('starred', 1)->orderBy('created_at', 'desc')->get();

        // Convert numeric 'starred' values to boolean
        $starredTrainers->transform(function ($trainer) {
            $trainer->starred = boolval($trainer->starred);
            return $trainer;
        });

        return response()->json($starredTrainers);
    }
}
