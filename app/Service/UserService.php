<?php

namespace App\Service;

//thực hiện các CRUD , gọi MODEL

use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserService
{
    //khai bao model
    protected $model;
    protected $modalPass;

    //tạo constructor, khởi tạo
    public function __construct(User $user, PasswordReset $passwordReset)
    {
        $this->model = $user;
        $this->modalPass = $passwordReset;
    }

    //register
    public function create($params)
    {
        try {
            //insert vô db trc ,tk chưa đc verify
            $user = $this->model->create($params);
        } catch (Exception $exception) {
            Log::error($exception);
            return false;
        }
        #hoặc viết kiểu truy vấn
        # DB::table('tên bảng')->insert('câu lệnh SQL')

        // Gửi email xác thực
        //event(new Registered($user));
        $user->sendEmailVerificationNotification();

        return [
            'message' => 'Register Successful. Please check your email to verify.'
        ];
    }

    //ham update
    public function update($product, $params)
    {
        try {
            Log::info('Param', $params);
            $result = $product->update($params);
        } catch (Exception $exception) {
            Log::error($exception);
            return false;
        }
        //trả về true nếu thành công, false nếu thất bại
        return $result;
    }

    //function login
    public function login($params)
    {
        //check email first
        $user = $this->model->where('email', $params['email'])->first();


        //check hash password
        $checkPass = Hash::check($params['password'], $user->password);

        if (!$checkPass) {
            return [
                'message' => 'Email or Password is incorrect',
                'code' => '404'
            ];
        }

        // Kiểm tra email đã được xác thực
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email has not verified yet'
            ], 403);
        }

        //create TOKEN by sanctum
        $token = $user->createToken('user')->plainTextToken;

        //create TOKEN by passport

        // Log::info('OAuth Request:', [
        //     'url' => config('app.url') . '/oauth/token',
        //     'params' => $params,
        //     'client_id' => config('services.passport.client_id'),
        //     'client_secret' => config('services.passport.client_secret'),

        // ]);

        // $http = new Client();

        // try {
        //     $response = $http->post(config('app.url') . '/oauth/token', [
        //         'form_params' => [
        //             'grant_type' => 'password',
        //             'client_id' => config('services.passport.client_id'),
        //             'client_secret' => config('services.passport.client_secret'),
        //             'username' => $params['email'],
        //             'password' => $params['password'],
        //             'scope' => '',
        //         ],
        //     ]);

        //     $token = json_decode((string) $response->getBody(), true);

        //     Log::info('OAuth Response:', $token);
        // } catch (\Exception $e) {
        //     Log::error('Generate token fail' + $e->getMessage());

        //     return response()->json([
        //         'message' => 'Unable to generate token',
        //         'error' => $e->getTraceAsString()

        //     ], 500);
        // }


        return response()->json([
            'message' => 'Login successful',
            'code' => '200 OK',
            //can create TOKEN if you want
            // 'access_token' => $token['access_token'],
            // 'refresh_token' => $token['refresh_token'],
            // 'expires_in' => $token['expires_in']
            'token'=>$token
        ], 200);
    }

    //verifyEmail
    public function verifyEmail($id, $hash)
    {
        //check email first
        $user = $this->model->findOrFail($id);

        // Kiểm tra hash verification URL hợp lệ
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => 'URL xác thực không hợp lệ'
            ], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email has verified already'
            ], 400);
        }

        // Thực hiện xác thực email
        if ($user->markEmailAsVerified()) {
            return response()->json([
                'message' => 'Verify email successful'
            ], 200);
        }

        return response()->json([
            'message' => 'Verify email unsuccess'
        ], 400);
    }

    //resendVerificationEmail
    public function resendVerificationEmail($params)
    {
        //check email first
        $user = $this->model->where('email', $params['email'])->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email đã được xác thực trước đó'
            ], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Email xác thực đã được gửi lại'
        ], 200);
    }

    //test auth TOKEN
    public function getList()
    {
        return $this->model->orderBy('id', 'desc')->get();
    }


    //send email forgot pass
    public function forgotPass($params)
    {
        try {
            //check email first
            $user = $this->model->where('email', $params['email'])->first();

            // Tạo token mới
            $token = $this->modalPass->createToken($params['email']);

            // Gửi email
            $user->notify(new ResetPassword($token));
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to send reset link',
                'error' => $e->getMessage()
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Reset password link sent to your email'
        ], 200);
    }


    //reset Pass
    public function resetPass($params)
    {
        try {
            // Kiểm tra token hợp lệ
            $reset = $this->modalPass->findValidToken(
                $params['token'],
                $params['email']
            );

            if (!$reset) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid token or expired'
                ], 400);
            }

            // Cập nhật mật khẩu
            $user = $this->model->where('email', $params['email'])->first();

            $user->password = $params['password'];

            $user->save();

            // Xóa token đã sử dụng
            $this->modalPass->invalidateToken($params['email']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to reset password',
                'error' => $e->getMessage()
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset successfully'
        ], 200);
    }
}
