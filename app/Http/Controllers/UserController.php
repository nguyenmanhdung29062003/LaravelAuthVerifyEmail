<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\User\SignInRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Service\UserService;

class UserController extends Controller
{

    //khai báo service
    protected $service;

    //tạo constructor
    public function __construct(UserService $userService)
    {
        $this->service = $userService;
    }

    //signIn
    public function signIn(SignInRequest $signInRequest)
    {
        //validation
        //createRequest rex tự lấy dữ liệu trên request và validate sau đó truyền vào param
        $params = $signInRequest->validated();


        //$param chính là dữ liệu được gửi
        //tiến hành gọi request để gọi service
        $result = $this->service->create($params);

        if ($result) {
            return new UserResource($result);
        }


        return response()->json(
            [
                'message' => 'Không thành công'
            ],
            400
        );
    }

    //logIn
    public function logIn(LoginRequest $logInRequest)
    {
        $params = $logInRequest->validated();

        $result = $this->service->login($params);

        return response()->json($result);
    }


    //test auth TOKEN
    public function getAll()
    {
        try {
            $result = $this->service->getList();

            return response()->json([
                'status' => 'success',
                'message' => 'Data retrieved successfully',
                'data' => $result,
                //show lấy thông tin user hiện tại qua TOKEN
                'user_moment'=> auth()->user()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}