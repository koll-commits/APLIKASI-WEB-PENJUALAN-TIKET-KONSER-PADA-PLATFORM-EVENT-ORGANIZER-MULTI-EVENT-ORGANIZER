<?php

use App\Models\Concert;
use App\Models\Tenant;
use Illuminate\Support\Facades\Route;

/**
 * Konteks OPERATOR TENANT (internal). Prefix: tenant/{tenant:slug}, name: tenant.*
 * Grup (middleware auth+verified+tenant/SetTenantContext + scopeBindings) didefinisikan tunggal di
 * PortalRoutes::tenant(); route fitur ditambahkan modul di app/Modules/{Modul}/routes/tenant.php.
 */
Route::get('/dashboard', function (Tenant $tenant) {
    // TenantContext sudah terisi middleware `tenant`, sehingga query Concert otomatis hanya milik EO ini.
    $stats = [
        'total' => Concert::query()->count(),
        'published' => Concert::query()->where('status', 'published')->count(),
        'draft' => Concert::query()->where('status', 'draft')->count(),
    ];
    $upcoming = Concert::query()
        ->where('status', '!=', 'cancelled')
        ->where('starts_at', '>=', now())
        ->orderBy('starts_at')
        ->limit(5)
        ->get();

    return view('tenant.dashboard', compact('tenant', 'stats', 'upcoming'));
})->name('dashboard');
