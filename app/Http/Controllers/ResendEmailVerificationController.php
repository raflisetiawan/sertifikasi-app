<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class ResendEmailVerificationController extends Controller
{

    public function verify(Request $request)
    {
        $user = User::find($request->input('id'));

        if ($request->input('id') != $user->getKey()) {
            throw new AuthorizationException;
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Link verifikasi dikirim kembali.'
        ], 200);
    }
}
