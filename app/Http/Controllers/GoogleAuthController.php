<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{

    // Phương thức chuyển hướng đến trang đăng nhập Google
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    // Phương thức xử lý callback sau khi xác thực Google
    public function callBackGoogle()
    {
        try {

            Log::info('Google Callback triggered');
            // Lấy thông tin người dùng từ Google sau khi xác thực
            $google_user = Socialite::driver('google')->user();

            //kiểm tra có trong DB hay chưa
            $user = User::where('google_id', $google_user->getId())->first();
            //chưa thì tạo
            if (!$user) {
                $new_user = User::create([
                    'name' => $google_user->getName(),
                    'email' => $google_user->getEmail(),
                    'google_id' => $google_user->getId(),
                ]);

                //create TOKEN by passport
                $tokenResult = $new_user->createToken('Personal Access Token', ['role:user']);
                $accessToken = $tokenResult->accessToken;
                $tokenExpiry = $tokenResult->token->expires_at;

                //tạo tk và login và đi đến trang home
                // Trả về response với token
                return response()->json([
                    'user' => $new_user,
                    'access_token' => $accessToken,
                    'expires_in' => $tokenExpiry,
                    'token_type' => 'Bearer',
                    // Có thể thêm redirect URL cho ứng dụng frontend
                ]);
            } else {
                // Nếu người dùng đã tồn tại
                // Tạo token cho người dùng 
                //create TOKEN by passport
                $tokenResult = $user->createToken('Personal Access Token', ['role:user']);
                $accessToken = $tokenResult->accessToken;
                $tokenExpiry = $tokenResult->token->expires_at;

                // Trả về response với token
                return response()->json([
                    'user' => $user,
                    'access_token' => $accessToken,
                    'expires_in' => $tokenExpiry,
                    'token_type' => 'Bearer',
                    // Có thể thêm redirect URL cho ứng dụng frontend
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Google Authentication Failed: ' . $e->getMessage());
            return response()->json(['error' => 'Authentication failed.'], 500);
        }
    }
}
