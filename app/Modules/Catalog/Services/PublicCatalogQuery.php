<?php

namespace App\Modules\Catalog\Services;

use App\Models\Catalog;
use App\Models\CatalogCategory;
use App\Models\Event;
use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

final class PublicCatalogQuery
{
    public function forEvent(Event $event): Collection
    {
        return Catalog::query()
            ->withoutGlobalScope('tenant')
            ->where('is_available', true)
            ->whereHas('tenant', fn ($q) => $q->where('event_id', $event->id)->where('status', 'active'))
            ->orderBy('name')
            ->get();
    }

    public function browse(Event $event, string $search = '', ?int $tenantId = null): LengthAwarePaginator
    {
        return Catalog::query()
            ->withoutGlobalScope('tenant')
            ->sellable()
            ->whereHas('tenant', fn ($q) => $q->where('event_id', $event->id)->where('status', 'active'))
            ->when($tenantId !== null, fn ($q) => $q->where('tenant_id', $tenantId))
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
            ->with(['tenant:id,display_name', 'category:id,name'])
            ->orderBy('name')
            ->paginate(16)
            ->withQueryString();
    }

    public function browseAll(Event $event, string $search = '', ?int $tenantId = null, ?string $category = null): Collection
    {
        return Catalog::query()
            ->withoutGlobalScope('tenant')
            ->whereHas('tenant', fn ($q) => $q->where('event_id', $event->id)->where('status', 'active'))
            ->when($tenantId !== null, fn ($q) => $q->where('tenant_id', $tenantId))
            ->when($category !== null && $category !== '', fn ($q) => $q->whereHas('category',
                fn ($c) => $c->withoutGlobalScope('tenant')->where('name', $category)))
            ->when($search !== '', function ($query) use ($search): void {
                $like = '%'.$search.'%';
                $query->where(function ($q) use ($like): void {
                    $q->where('name', 'like', $like)
                      ->orWhereHas('tenant', fn ($t) => $t->where('display_name', 'like', $like))
                      ->orWhereHas('category', fn ($c) => $c->withoutGlobalScope('tenant')->where('name', 'like', $like));
                });
            })
            ->with(['tenant:id,display_name,slug', 'category' => fn ($q) => $q->withoutGlobalScope('tenant')->select('id', 'name')])
            ->orderBy('tenant_id')->orderBy('name')
            ->get();
    }

    public function categoryNames(Event $event): SupportCollection
    {
        return CatalogCategory::query()->withoutGlobalScope('tenant')
            ->whereHas('tenant', fn ($q) => $q->where('event_id', $event->id)->where('status', 'active'))
            ->orderBy('name')->distinct()->pluck('name');
    }

    public function activeTenants(Event $event): SupportCollection
    {
        return Tenant::query()
            ->where('event_id', $event->id)
            ->where('status', 'active')
            ->orderBy('display_name')
            ->get(['id', 'display_name']);
    }
}