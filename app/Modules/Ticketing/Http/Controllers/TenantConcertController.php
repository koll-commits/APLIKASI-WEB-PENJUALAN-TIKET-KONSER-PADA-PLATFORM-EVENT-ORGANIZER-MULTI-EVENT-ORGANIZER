<?php

namespace App\Modules\Ticketing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Concert;
use App\Models\Tenant;
use App\Modules\Ticketing\Http\Requests\ConcertRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

/**
 * CRUD event/konser milik EO. Data difilter global scope TenantContext; {concert} ter-scope
 * di bawah {tenant} (scoped binding) dan aksinya diperiksa ConcertPolicy.
 */
final class TenantConcertController extends Controller
{
    use AuthorizesRequests;

    public function index(Tenant $tenant): View
    {
        $this->authorize('viewAny', Concert::class);
        $concerts = Concert::query()->orderByDesc('starts_at')->get();

        return view('ticketing::concerts.index', compact('tenant', 'concerts'));
    }

    public function create(Tenant $tenant): View
    {
        $this->authorize('create', Concert::class);

        return view('ticketing::concerts.create', compact('tenant'));
    }

    public function store(ConcertRequest $request, Tenant $tenant): RedirectResponse
    {
        $concert = Concert::create($request->validated() + ['slug' => $this->uniqueSlug($request->validated('name'))]);

        return redirect()->route('tenant.concerts.index', ['tenant' => $tenant->slug])
            ->with('status', "Event {$concert->name} dibuat.");
    }

    public function edit(Tenant $tenant, Concert $concert): View
    {
        $this->authorize('update', $concert);

        return view('ticketing::concerts.edit', compact('tenant', 'concert'));
    }

    public function update(ConcertRequest $request, Tenant $tenant, Concert $concert): RedirectResponse
    {
        $concert->update($request->validated());

        return redirect()->route('tenant.concerts.index', ['tenant' => $tenant->slug])
            ->with('status', "Event {$concert->name} diperbarui.");
    }

    public function destroy(Tenant $tenant, Concert $concert): RedirectResponse
    {
        $this->authorize('delete', $concert);

        // Event yang sudah terbit/dibatalkan tidak dihapus agar riwayat tetap utuh.
        if ($concert->status !== 'draft') {
            return back()->withErrors(['concert' => 'Hanya event berstatus Draf yang bisa dihapus. Ubah status menjadi Dibatalkan bila perlu.']);
        }

        $concert->delete();

        return redirect()->route('tenant.concerts.index', ['tenant' => $tenant->slug])
            ->with('status', 'Event dihapus.');
    }

    /** Slug unik per EO (scope tenant aktif), mis. "rock-fest", "rock-fest-2". */
    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'event';
        $slug = $base;
        $i = 2;
        while (Concert::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
