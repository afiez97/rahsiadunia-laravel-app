<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HutangController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('notes', \App\Http\Controllers\NoteController::class);
    Route::resource('accounts', \App\Http\Controllers\AccountController::class);
    Route::resource('sheets', \App\Http\Controllers\GoogleSheetController::class);

    // Hutang Tracker
    Route::resource('hutang', HutangController::class);
    Route::post('hutang/{hutang}/payments',                   [HutangController::class, 'storePayment'])->name('hutang.payment.store');
    Route::post('hutang/{hutang}/installments/{installment}', [HutangController::class, 'markInstallmentPaid'])->name('hutang.installment.pay');
    Route::post('hutang/{hutang}/regenerate-invite',          [HutangController::class, 'regenerateInvite'])->name('hutang.invite.regenerate');
    Route::post('hutang/{hutang}/unlink-contact',             [HutangController::class, 'unlinkContact'])->name('hutang.contact.unlink');
});

require __DIR__.'/auth.php';
