<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Catalog;
use App\Models\Tenant;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint contoh untuk membuktikan isolasi (global scope + scoped binding + policy).
 * UI Livewire penuh menyusul Modul 7; di sini fokus pada pembuktian keamanan.
 */
final class TenantCatalogController extends Controller
{
    use AuthorizesRequests;

    public function index(Tenant $tenant): JsonResponse
    {
        // Global scope memfilter ke tenant aktif (TenantContext dari middleware).
        $catalogs = Catalog::query()->orderBy('name')->get(['id', 'name', 'base_price', 'is_available']);

        return response()->json(['tenant' => $tenant->slug, 'catalogs' => $catalogs]);
    }

    public function show(Tenant $tenant, Catalog $catalog): JsonResponse
    {
        $this->authorize('view', $catalog);

        return response()->json($catalog->only(['id', 'name', 'base_price', 'is_available']));
    }

    public function update(Request $request, Tenant $tenant, Catalog $catalog): JsonResponse
    {
        $this->authorize('update', $catalog);

        $data = $request->validate(['is_available' => ['required', 'boolean']]);
        $catalog->update(['is_available' => $data['is_available']]);

        return response()->json(['ok' => true, 'is_available' => $catalog->is_available]);
    }
}
