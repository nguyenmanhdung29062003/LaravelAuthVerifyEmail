<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\RefreshToken;

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
        //get access TOKEN from header
        $accessToken = $request->bearerToken();

        //check if accessToken is out of date, and it is a TOKEN
        if ($accessToken && $this->isTokenExpired($accessToken)) {
            $newToken = $this->refreshToken($accessToken);

            if($newToken){
                $request->headers->set('Authorization', 'Bearer ' . $newToken['access_token']);
            }
        }
        return $next($request);
    }

    protected function isTokenExpired($accessToken)
    {
        return $accessToken->expires_at < now();
    }

    //function get new Access TOKEN from Refresh TOKEN 
    protected function refreshToken($accessToken)
    {
        $refreshToken = RefreshToken::where('access_token_id', $accessToken->id)->where('revoked', false)->first();

        try {
            $response = Http::asForm()->post(config('app.url') . '/oauth/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken->id,
                'client_id' => config('services.passport.client_id'),
                'client_secret' => config('services.passport.client_secret'),

            ]);

            if($response->successful())
            {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Refresh token fail' + $e->getMessage());
        }

        return null;
    }
}
