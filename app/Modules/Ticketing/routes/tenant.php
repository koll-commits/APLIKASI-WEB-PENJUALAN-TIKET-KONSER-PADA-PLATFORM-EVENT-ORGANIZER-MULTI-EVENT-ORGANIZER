<?php

use App\Modules\Ticketing\Http\Controllers\TenantConcertController;
use App\Support\Routing\PortalRoutes;
use Illuminate\Support\Facades\Route;

/**
 * Route portal EO milik modul Ticketing: CRUD event/konser. {concert} ter-scope di bawah
 * {tenant} (scopeBindings dari PortalRoutes) dan difilter global scope TenantContext.
 */
PortalRoutes::tenant(function (): void {
    Route::get('/concerts', [TenantConcertController::class, 'index'])->name('concerts.index');
    Route::get('/concerts/create', [TenantConcertController::class, 'create'])->name('concerts.create');
    Route::post('/concerts', [TenantConcertController::class, 'store'])->name('concerts.store');
    Route::get('/concerts/{concert}/edit', [TenantConcertController::class, 'edit'])->name('concerts.edit');
    Route::patch('/concerts/{concert}', [TenantConcertController::class, 'update'])->name('concerts.update');
    Route::delete('/concerts/{concert}', [TenantConcertController::class, 'destroy'])->name('concerts.destroy');
});
