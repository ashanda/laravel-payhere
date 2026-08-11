<?php

use Geekmac\PayHere\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// Session-based pages: run through the 'web' middleware group as normal.
Route::middleware('web')->group(function () {
    Route::get('/payment/checkout', [PaymentController::class, 'checkout'])->name('payhere.checkout');
    Route::get('/payment/success', [PaymentController::class, 'success'])->name('payhere.success');
    Route::get('/payment/cancel', [PaymentController::class, 'cancel'])->name('payhere.cancel');
});

// Server-to-server webhook: deliberately NOT in the 'web' group, so it is
// not subject to CSRF verification (PayHere sends no CSRF token).
// Signature is verified inside the controller via PayHereService::verifyNotification().
Route::post('/payment/notify', [PaymentController::class, 'notify'])->name('payhere.notify');
