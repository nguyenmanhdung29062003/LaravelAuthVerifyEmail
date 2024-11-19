<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Events\Verified;

class EmailVerificationController extends Controller
{
    // Gửi email xác thực
    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email đã được xác thực'
            ]);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Link xác thực đã được gửi'
        ]);
    }

    // Verify email
    public function verify(Request $request)
    {
        $user = User::findOrFail($request->route('id'));

        // Kiểm tra URL xác thực hợp lệ
        if (!URL::hasValidSignature($request)) {
            return response()->json([
                'message' => 'Link xác thực không hợp lệ'
            ], 400);
        }

        // Kiểm tra email chưa được xác thực
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email đã được xác thực trước đó'
            ]);
        }

        // Đánh dấu email đã xác thực
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'message' => 'Email đã được xác thực thành công'
        ]);
    }
}
