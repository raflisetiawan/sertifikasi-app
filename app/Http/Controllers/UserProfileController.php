<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    /**
     * Display the user profile.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return new UserResource(true, 'User Profile', $user);
    }

    /**
     * Update the user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email,' . $id,
            'phone_number' => 'string|max:20',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($user->image) {
                Storage::delete('public/users/' . basename($user->image));
            }

            // Store the new image
            $image = $request->file('image');
            $image->storeAs('public/users', $image->hashName());
            $user->update(['image' => $image->hashName()]);
        }

        // Update user profile information
        $user->update([
            'name' => $request->input('name', $user->name),
            'email' => $request->input('email', $user->email),
            'phone_number' => $request->input('phone_number', $user->phone_number),
        ]);

        return new UserResource(true, 'User Profile Updated', $user);
    }

    public function getProfile()
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'image' => $user->image
            ]
        ]);
    }


    /**
     * Remove the user profile image.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function removeImage($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Delete the user's profile image
        if ($user->image) {
            Storage::delete('public/users/' . basename($user->image));
            $user->update(['image' => null]);
        }

        return new UserResource(true, 'User Profile Image Removed', $user);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone_number' => 'sometimes|string|max:20',
            'current_password' => 'required_with:new_password',
            'new_password' => 'sometimes|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Check current password if trying to change password
            if ($request->has('new_password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Current password is incorrect'
                    ], 422);
                }

                $user->password = Hash::make($request->new_password);
            }

            // Update other fields
            if ($request->has('name')) {
                $user->name = $request->name;
            }
            if ($request->has('phone_number')) {
                $user->phone_number = $request->phone_number;
            }

            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'image' => $user->image
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile'
            ], 500);
        }
    }

    public function updateImage(Request $request)
    {
        Log::debug($request->all());
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($user->image) {
                Storage::delete('public/users/' . basename($user->image));
            }

            // Store the new image
            $image = $request->file('image');
            $image->storeAs('public/users', $image->hashName());
            $user->update(['image' => $image->hashName()]);
        }

        return new UserResource(true, 'User Profile Image Updated', $user);
    }

}
