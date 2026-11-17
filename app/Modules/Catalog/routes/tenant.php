<?php
use App\Models\Tenant;
use App\Modules\Catalog\Http\Controllers\TenantCatalogController;
use App\Support\Routing\PortalRoutes;
use Illuminate\Support\Facades\Route;

/**
 * Route portal tenant milik modul Catalog. {catalog} ter-scope di bawah {tenant} (scopeBindings
 * dari PortalRoutes) dan difilter global scope TenantContext.
 */
PortalRoutes::tenant(function (): void {
    Route::get('/catalogs', [TenantCatalogController::class, 'index'])->name('catalogs.index');
    Route::get('/catalogs/{catalog}', [TenantCatalogController::class, 'show'])->name('catalogs.show');
    Route::patch('/catalogs/{catalog}', [TenantCatalogController::class, 'update'])->name('catalogs.update');
    Route::get('/catalog-manager', fn (Tenant $tenant) => view('catalog::tenant.catalog-manager', ['tenant' => $tenant]))
        ->name('catalog-manager');
});
