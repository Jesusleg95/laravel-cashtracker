<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\LogoutController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

// Pantalla de regristro
Route::get('/auth/register', [RegisterController::class, 'index'])->name('register');
Route::post('/auth/register', [RegisterController::class, 'store'])->name('register.store');

// Pantalla de iniciar sesion
Route::get('/auth/login', [LoginController::class, 'index'])->name('login');
Route::post('/auth/login', [LoginController::class, 'store'])->name('login.store');

// Cerrar Sesion
Route::post('/auth/logout', [LogoutController::class, 'store'])->name('logout.store');

// Verificacion
Route::get('/email/verify/{id}/{hash}', function(EmailVerificationRequest $request){
    $request->fulfill();

    return redirect()->route('dashboard')->with('success', 'Tu correo fue verificado correctamente.');
})->middleware(['auth', 'signed'])->name('verification.verify');

// Pantalla de verificar cuenta
Route::get('/email/verify', function(){
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::post('/email/verification-notification', function(Request $request){
    $request->user()->sendEmailVerificationNotification();

    return back()->with('suceess', 'Se ha reenviado el correo de verificación');
})->middleware(['auth', 'throttle:1,1'])->name('verification.send');

Route::get('/dashboard', [BudgetController::class,'index' ])->middleware(['auth', 'verified'])->name('dashboard');