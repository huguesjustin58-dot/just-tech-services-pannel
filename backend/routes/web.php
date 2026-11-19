<?php

declare(strict_types=1);

use App\Http\Controllers\Client\ClientWorkspaceController;
use App\Http\Controllers\PublicShowcaseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Niveau 1 — Vitrine Publique (/)
|--------------------------------------------------------------------------
| Accessible à tous sans authentification (aucune redirection forcée vers /login).
*/
Route::get('/', [PublicShowcaseController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Authentification Client (/login, /register, /logout)
|--------------------------------------------------------------------------
| L'inscription publique force strictement le rôle 'client'.
*/
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Niveau 2 — Espace Client (/dashboard & /client/...)
|--------------------------------------------------------------------------
| Protégé par auth + EnsureUserIsClient ('client') + Policies par ressource.
*/
Route::middleware(['auth', 'client'])->group(function (): void {
    Route::get('/dashboard', static fn () => redirect()->route('client.dashboard'))->name('dashboard');

    Route::prefix('client')->name('client.')->group(function (): void {
        Route::get('/dashboard', [ClientWorkspaceController::class, 'dashboard'])->name('dashboard');
        Route::post('/payments', [ClientWorkspaceController::class, 'submitPayment'])->name('payments.store');
        Route::post('/free-test', [ClientWorkspaceController::class, 'claimFreeTest'])->name('free-test.claim');
        Route::post('/licenses/{license}/android', [ClientWorkspaceController::class, 'issueAndroidCloud'])->name('delivery.android');
        Route::get('/licenses/{license}/iphone', [ClientWorkspaceController::class, 'downloadIphoneNpv'])->name('delivery.iphone');
    });
});
