<?php

use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\GoogleAuthController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


//check role
//->middleware(['role:admin'])
Route::get('/getAll', [UserController::class, 'getAll'])->middleware(['auth:api', 'refresh.token', 'role:user']);

//Auth Verify Email
Route::prefix('auth')->group(function () {
    #route start
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::get('/email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/resend-verification', [UserController::class, 'resendVerificationEmail']);


    //Logout
    Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:api');
});


//Forgot pass
#route start
Route::post('forgot-password', [UserController::class, 'forgotPassword']);

Route::get('show-form', function () {
    return view('resetPass');
})->name('password.reset');

Route::post('reset-password', [UserController::class, 'resetPassword'])->name('password.update');

//Login Social "account gg"
#route start , sd ui blade loginSocial để test call API
// Route::middleware('web')->group(function () {
//     Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->name('google-auth');
// });

// Route::get('auth/google/call-back', [GoogleAuthController::class, 'callBackGoogle']);
