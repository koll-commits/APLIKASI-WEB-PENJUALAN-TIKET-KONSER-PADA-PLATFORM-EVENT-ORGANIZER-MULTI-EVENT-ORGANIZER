<?php

use App\Modules\Catalog\Http\Controllers\TenantCatalogController;
use App\Support\Routing\PortalRoutes;
use Illuminate\Support\Facades\Route;

/**
 * Route portal tenant milik modul Catalog. {catalog} ter-scope di bawah {tenant} (scopeBindings
 * dari PortalRoutes) dan difilter global scope TenantContext.
 */
PortalRoutes::tenant(function (): void {
    Route::get('/catalogs', [TenantcatalogController::class, 'index'])->name('catalogs.index');
    Route::get('/catalogs/{catalog}', [TenantcatalogController::class, 'show'])->name('catalogs.show');
    Route::patch('/catalogs/{catalog}', [TenantcatalogController::class, 'update'])->name('catalogs.update');
});
