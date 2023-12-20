<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
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
}
