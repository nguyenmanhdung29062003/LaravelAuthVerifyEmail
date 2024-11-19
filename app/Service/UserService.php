<?php

namespace App\Service;

//thực hiện các CRUD , gọi MODEL

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    //khai bao model
    protected $model;

    //tạo constructor, khởi tạo
    public function __construct(User $user)
    {
        $this->model = $user;
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

        return response()->json([
            'message' => 'Login successful',
            'code' => '200 OK',
            //can create TOKEN if you want
            'access_token' => $user->createToken('user')->plainTextToken
        ],200);
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

    // Gửi email xác thực
    public function sendVerificationEmail($params)
    {
        $user = $this->model->where('email', $params['email'])->first();

        // if ($user->hasVerifiedEmail) {
        //     return response()->json([
        //         'message' => 'Email đã được xác thực'
        //     ],200);
        // }

        $user->sendEmailVerificationNotification();

        return [
            'message' => 'Link had been send'
        ];
    }

    // Verify email
    public function verify($params)
    {
        $user = $this->model->where('email', $params['email'])->first();


        // Kiểm tra email chưa được xác thực
        if (!$user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email is not verified.'], 403);
        }

        //Đánh dấu email đã xác thực
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'message' => 'Email đã được xác thực thành công'
        ]);
    }
}
