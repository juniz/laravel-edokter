<?php

use App\Http\Controllers\Api\Payment\MidtransWebhookController;
use App\Http\Controllers\Api\Rdash\AccountController;
use App\Http\Controllers\Api\Rdash\DomainController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RDASH API Routes
|--------------------------------------------------------------------------
|
| Routes untuk integrasi dengan RDASH API
| Semua endpoint menggunakan prefix /api/rdash
|
*/

// Payment webhook routes (no auth required, verified via signature)
Route::prefix('payments')->name('payments.')->group(function () {
    Route::post('/midtrans/webhook', [MidtransWebhookController::class, 'handle'])->name('midtrans.webhook');
});

Route::prefix('rdash')->name('rdash.')->group(function () {
    // Account routes
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
        Route::get('/balance', [AccountController::class, 'balance'])->name('balance');
        Route::get('/prices', [AccountController::class, 'prices'])->name('prices');
        Route::get('/prices/{priceId}', [AccountController::class, 'price'])->name('price');
        Route::get('/transactions', [AccountController::class, 'transactions'])->name('transactions');
        Route::get('/transactions/{transactionId}', [AccountController::class, 'transaction'])->name('transaction');
    });

    // Domain routes
    Route::prefix('domains')->name('domains.')->group(function () {
        Route::get('/', [DomainController::class, 'index'])->name('index');
        Route::get('/{domainId}', [DomainController::class, 'show'])->name('show');
        Route::get('/availability/check', [DomainController::class, 'availability'])->name('availability');
        Route::get('/whois/check', [DomainController::class, 'whois'])->name('whois');
        Route::post('/register', [DomainController::class, 'register'])->name('register');
        Route::post('/transfer', [DomainController::class, 'transfer'])->name('transfer');
        Route::post('/{domainId}/renew', [DomainController::class, 'renew'])->name('renew');
        Route::put('/{domainId}/nameservers', [DomainController::class, 'updateNameservers'])->name('nameservers.update');
        Route::get('/{domainId}/auth-code', [DomainController::class, 'authCode'])->name('auth-code');
        Route::put('/{domainId}/auth-code', [DomainController::class, 'resetAuthCode'])->name('auth-code.reset');
        Route::put('/{domainId}/lock', [DomainController::class, 'lock'])->name('lock');
        Route::delete('/{domainId}/lock', [DomainController::class, 'unlock'])->name('unlock');
        Route::put('/{domainId}/suspend', [DomainController::class, 'suspend'])->name('suspend');
        Route::delete('/{domainId}/suspend', [DomainController::class, 'unsuspend'])->name('unsuspend');
    });
});
