<?php

namespace App\Http\Controllers;

use App\Events\AssignUserRole;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function sign_up(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
            'phone_number' => [
                'required',
                'regex:/^(?:\+62|0)[0-9]{8,15}$/'
            ],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
        ]);
        event(new Registered($user));
        event(new AssignUserRole($user));

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => $user,
        ], 201);
    }

    public function sign_in(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response([
                'success' => false,
                'message' => 'Email tidak terdaftar'
            ], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response([
                'success' => false,
                'message' => 'Email atau password yang Anda masukkan salah.'
            ], 401);
        }

        $token = $user->createToken('ApiToken')->plainTextToken;

        $response = [
            'success' => true,
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    public function sign_out()
    {
        auth()->logout();
        return response()->json([
            'success'    => true
        ], 200);
    }

    public function getUserWithRole(Request $request)
    {
        // Pastikan pengguna sudah diautentikasi untuk mengakses data pengguna dengan peran.
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Ambil peran pengguna
        $role = $user->role;

        // Anda dapat mengakses data peran melalui $role, misalnya:
        $roleName = $role->name; // Nama peran pengguna

        // Menggabungkan data pengguna dan peran dalam respons
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'role' => $roleName, // Menambahkan nama peran
            'email_verified_at' => $user->email_verified_at,
        ];

        return response()->json([
            'user' => $userData,
        ], 200);
    }
}
