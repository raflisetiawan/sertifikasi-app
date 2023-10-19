<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'message' => 'User registered successfully.',
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat memproses pendaftaran. Silakan coba lagi nanti.'
            ], 500);
        }
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
}
