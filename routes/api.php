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

//test auth TOKEN
//->middleware(['auth', 'verified']);
Route::get('/getAll', [UserController::class, 'getAll'])->middleware('auth:sanctum');

//Auth Verify EmailEmail
Route::prefix('auth')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::get('/email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])->middleware(['signed'])->name('verification.verify');
    Route::post('/resend-verification', [UserController::class, 'resendVerificationEmail']);
});

//Forgot pass
Route::post('forgot-password', [UserController::class, 'forgotPassword']);

Route::get('show-form',function(){
    return view('resetPass');
})->name('password.reset');

Route::post('reset-password', [UserController::class, 'resetPassword'])->name('password.update');

