<?php

namespace App\Http\Controllers;

use App\Events\AssignUserRole;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

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

        $user->tokens()
            ->where('expires_at', '<', now())
            ->delete();

        $token = $user->createToken('ApiToken')->plainTextToken;

        return response([
            'success' => true,
            'user' => $user,
            'token' => $token,
            'expires_at' => now()->addHours(24)->toDateTimeString()
        ], 201);

        return response($response, 201);
    }

    public function sign_out(Request $request)
    {
        try {
            // Get authorization header
            $authHeader = $request->header('Authorization');

            if (!$authHeader) {
                return response()->json([
                    'success' => false,
                    'message' => 'No authorization header found'
                ], 401);
            }

            // Extract token from Bearer string
            $accessToken = str_replace('Bearer ', '', $authHeader);

            if (!$accessToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'No token provided'
                ], 401);
            }

            // Find and delete the token directly from the database
            $tokenId = explode('|', $accessToken)[0];
            $deleted = DB::table('personal_access_tokens')
                ->where('id', $tokenId)
                ->delete();

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token not found or already revoked'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Logout failed:', [
                'error' => $e->getMessage(),
                'auth_header' => $request->header('Authorization')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to logout',
                'error' => $e->getMessage()
            ], 500);
        }
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

    public function googleRedirect()
    {
        try {
            $url = Socialite::driver('google')
                ->stateless()  // Add this
                ->redirect()
                ->getTargetUrl();

            return response()->json([
                'success' => true,
                'url' => $url
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate Google auth URL',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function googleCallback(Request $request)
    {
        Log::debug($request->all());
        try {
            // Validate code parameter exists
            if (!$request->has('code')) {
                throw new \Exception('Authorization code not provided');
            }

            // Get user details from Google
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            // Find or create user
            $user = User::where('email', $googleUser->email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'password' => bcrypt(Str::random(16))
                ]);

                event(new Registered($user));
                event(new AssignUserRole($user));
            }

            // Generate token and redirect
            $token = $user->createToken('ApiToken')->plainTextToken;

            return redirect()->away(env('FRONTEND_CALLBACK_URL') . '?token=' . $token);
        } catch (\Exception $e) {
            Log::error('Google callback error: ' . $e->getMessage());
            return redirect()->away(
                env('FRONT_URL') . '/auth/error?message=' . urlencode($e->getMessage())
            );
        }
    }
    public function refreshToken(Request $request)
    {
        Log::debug($request);
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            // Create new token
            $token = $user->createToken('ApiToken')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'expires_at' => now()->addHours(24)->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh token',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
