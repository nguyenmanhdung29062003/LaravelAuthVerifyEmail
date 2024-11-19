<?php

use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/signin', [UserController::class, 'signIn']);

Route::get('/login', [UserController::class, 'logIn']);

//test auth TOKEN
//->middleware(['auth', 'verified']);
Route::get('/getAll', [UserController::class, 'getAll'])->middleware('auth:sanctum');

//Verify
Route::prefix('auth')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::get('/email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/resend-verification', [UserController::class, 'resendVerificationEmail']);
});

Route::post('/sendEmail', [UserController::class, 'sendEmail']);

//->middleware(['signed'])