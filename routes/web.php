<?php

use App\Http\Controllers\GoogleAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('loginSocial');
});

Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->name('google-auth');

Route::get('auth/google/call-back', [GoogleAuthController::class, 'callBackGoogle']);
