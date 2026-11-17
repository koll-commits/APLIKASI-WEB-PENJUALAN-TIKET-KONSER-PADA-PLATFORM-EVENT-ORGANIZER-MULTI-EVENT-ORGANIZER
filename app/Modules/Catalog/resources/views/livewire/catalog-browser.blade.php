<?php

use App\Models\Event;
use App\Modules\Catalog\Services\PublicCatalogQuery;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    public string $eventSlug = '';

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $tenantId = null;

    #[Url]
    public ?string $category = null;

    public function mount(string $eventSlug): void
    {
        $this->eventSlug = $eventSlug;
    }

    public function filterCategory(?string $name): void
    {
        $this->category = $name;
    }

    #[Computed]
    public function event(): ?Event
    {
        return Event::query()->where('slug', $this->eventSlug)->first();
    }

    #[Computed]
    public function groups(): \Illuminate\Support\Collection
    {
        $event = $this->event();
        return $event
            ? app(PublicCatalogQuery::class)->browseAll($event, $this->search, $this->tenantId, $this->category)->groupBy('tenant_id')
            : collect();
    }

    #[Computed]
    public function tenants(): \Illuminate\Support\Collection
    {
        $event = $this->event();
        return $event ? app(PublicCatalogQuery::class)->activeTenants($event) : collect();
    }

    #[Computed]
    public function categories(): \Illuminate\Support\Collection
    {
        $event = $this->event();
        return $event ? app(PublicCatalogQuery::class)->categoryNames($event) : collect();
    }
};
?>

<div class="space-y-4" wire:poll.5s>
    @if (! $this->event)
        <x-empty-state title="Event tidak ditemukan" description="Pilih event yang valid untuk melihat katalog." />
    @else
        <div class="flex flex-col gap-2">
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari tiket, merchandise, tenant…"
                class="min-h-11 rounded-lg border border-zinc-300 bg-white px-3 text-sm dark:border-zinc-600 dark:bg-zinc-800" />

            <select wire:model.live="tenantId" aria-label="Saring tenant"
                class="min-h-11 rounded-lg border border-zinc-300 bg-white px-3 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                <option value="">Semua Tenant / EO</option>
                @foreach ($this->tenants as $tenant)
                    <option value="{{ $tenant->id }}" wire:key="tenant-{{ $tenant->id }}">{{ $tenant->display_name }}</option>
                @endforeach
            </select>

            <div class="flex gap-2 overflow-x-auto pb-1">
                <button type="button" wire:click="filterCategory(null)"
                    @class(['min-h-9 shrink-0 rounded-full border px-3 text-sm', 'border-red-600 bg-red-600 text-white' => ! $category, 'border-zinc-300' => $category])>
                    Semua
                </button>
                @foreach ($this->categories as $name)
                    <button type="button" wire:key="chip-{{ $name }}" wire:click="filterCategory(@js($name))"
                        @class(['min-h-9 shrink-0 rounded-full border px-3 text-sm', 'border-red-600 bg-red-600 text-white' => $category === $name, 'border-zinc-300' => $category !== $name])>
                        {{ $name }}
                    </button>
                @endforeach
            </div>
        </div>

        @forelse ($this->groups as $items)
            @php($tenant = $items->first()->tenant)
            <section wire:key="group-{{ $tenant->id }}" class="mt-4">
                <div class="flex items-baseline justify-between border-b-2 border-zinc-900 pb-1 dark:border-zinc-100">
                    <h2 class="text-sm font-extrabold uppercase tracking-wide">{{ $tenant->display_name }}</h2>
                </div>
                <ul class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @foreach ($items as $item)
                        @php($sellable = $item->is_available && $item->stock_qty > 0)
                        <li wire:key="catalog-{{ $item->id }}" @class(['flex items-center justify-between py-3', 'opacity-50' => ! $sellable])>
                            <div>
                                <p class="font-semibold">{{ $item->name }}</p>
                                <p class="text-xs text-zinc-500">{{ $item->category?->name }}</p>
                                <p class="text-sm font-semibold text-red-600">Rp {{ number_format($item->base_price, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                @if ($sellable)
                                    @auth
                                        <button type="button" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">
                                            + Pilih Tiket
                                        </button>
                                    @else
                                        <a href="{{ route('login') }}" class="rounded-lg border border-red-600 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40">
                                            Login untuk Beli
                                        </a>
                                    @endauth
                                @else
                                    <span class="rounded border border-zinc-300 px-2 py-0.5 text-xs font-bold text-zinc-600">HABIS</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <x-empty-state title="Katalog tidak ditemukan" description="Coba kata kunci atau kategori lain." />
        @endforelse
    @endif
</div>