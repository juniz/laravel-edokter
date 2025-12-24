<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    // Email check and resume routes
    Route::post('register/check-email', [RegisteredUserController::class, 'checkEmail'])
        ->name('register.check-email');
    Route::post('register/resume', [RegisteredUserController::class, 'resume'])
        ->name('register.resume');
    Route::post('register/start-new', [RegisteredUserController::class, 'startNew'])
        ->name('register.start-new');

    // Step validation routes
    Route::post('register/validate-step1', [RegisteredUserController::class, 'validateStep1'])
        ->name('register.validate-step1');
    Route::post('register/validate-step2', [RegisteredUserController::class, 'validateStep2'])
        ->name('register.validate-step2');

    // Email verification routes
    Route::post('email-verification/send', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'send'])
        ->name('email-verification.send');
    Route::post('email-verification/verify', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'verify'])
        ->name('email-verification.verify');
    Route::post('email-verification/resend', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'resend'])
        ->name('email-verification.resend');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
