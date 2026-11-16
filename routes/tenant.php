<?php

use App\Models\Event;
use App\Models\Tenant;
use Illuminate\Support\Facades\Route;

/**
 * Konteks OPERATOR TENANT (internal). Prefix: tenant/{tenant:slug}, name: tenant.*
 * Grup (middleware auth+verified+tenant/SetTenantContext + scopeBindings) didefinisikan tunggal di
 * PortalRoutes::tenant(); route fitur ditambahkan modul di app/Modules/{Modul}/routes/tenant.php.
 */
Route::get('/dashboard', function (Tenant $tenant) {
    $stats = [
        'total' => Event::query()->where('tenant_id', $tenant->id)->count(),
        'published' => Event::query()->where('tenant_id', $tenant->id)->where('status', 'published')->count(),
        'draft' => Event::query()->where('tenant_id', $tenant->id)->where('status', 'draft')->count(),
    ];
    $upcoming = Event::query()
        ->where('tenant_id', $tenant->id)
        ->where('status', '!=', 'cancelled')
        ->where('starts_at', '>=', now())
        ->orderBy('starts_at')
        ->limit(5)
        ->get();

    return view('tenant.dashboard', compact('tenant', 'stats', 'upcoming'));
})->name('dashboard');