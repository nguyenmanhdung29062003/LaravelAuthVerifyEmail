<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\TokenRepository;

class RefreshTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Lấy Authorization header
        $authorizationHeader = $request->header('Authorization');

        if ($authorizationHeader && str_starts_with($authorizationHeader, 'Bearer ')) {
            // Trích xuất token từ header
            $token = trim(str_replace('Bearer', '', $authorizationHeader));

            // Lấy thông tin token từ database
            $tokenModel = app(TokenRepository::class)->find($token);

            if ($tokenModel) {
                // Kiểm tra token có gần hết hạn 
                $expiresAt = Carbon::parse($tokenModel->expires_at);
                if (Carbon::now()->diffInMinutes($expiresAt) <= 60) {
                    // Revoke token cũ
                    $tokenModel->revoke();

                    // Lấy người dùng từ token
                    $user = $tokenModel->user;

                    // Tạo token mới
                    $newToken = $user->createToken('Personal Access Token', $tokenModel->scopes);
                    $newAccessToken = $newToken->accessToken;

                    // Cập nhật token mới trong header request
                    $request->headers->set('Authorization', 'Bearer ' . $newAccessToken);

                    // Đính kèm token mới vào response (nếu cần)
                    $request->attributes->set('new_access_token', $newAccessToken);
                }
            }
        }

        return $next($request);
    }
}
