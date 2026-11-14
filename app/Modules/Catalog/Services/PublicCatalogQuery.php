<?php

namespace App\Modules\Catalog\Services;

use App\Models\Catalog;
use App\Models\Event;
use App\Models\Menu;
use Illuminate\Support\Collection;

/**
 * Query lintas-tenant yang SAH untuk katalog publik satu kantin. Bypass global scope
 * dilakukan eksplisit lalu diganti filter pengganti: hanya tenant milik canteen tsb,
 * berstatus aktif, dan menu yang tersedia. Ditempatkan di class khusus agar mudah diaudit.
 */
final class PublicCatalogQuery
{
    /** @return Collection<int, Event> */
    public function forEvent(Event $event): Collection
    {
        return Catalog::query()
            ->withoutGlobalScope('tenant') // bypass eksplisit, bukan diam-diam
            ->where('is_available', true)
            ->whereHas('tenant', function ($query) use ($event): void {
                $query->where('event_id', $event->id)->where('status', 'active');
            })
            ->orderBy('name')
            ->get();
    }
}
