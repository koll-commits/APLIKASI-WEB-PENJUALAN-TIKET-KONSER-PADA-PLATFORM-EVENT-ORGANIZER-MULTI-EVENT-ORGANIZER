<?php

use App\Models\Catalog;
use App\Models\CatalogCategory;
use App\Models\Tenant;
use App\Models\UserTenantRole;
use App\Modules\Catalog\Services\CatalogStockService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public int $tenantId = 0;
    public string $name = '';
    public ?int $categoryId = null;
    public int $basePrice = 0;
    public int $prepMinutes = 10;
    public int $stockQty = 0;
    public string $newCategory = '';
    public string $search = '';
    public ?int $filterCategory = null;

    public function mount(int $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function booted(): void
    {
        $user = Auth::user();
        $isMember = $user !== null && UserTenantRole::query()
            ->where('user_id', $user->id)
            ->where('tenant_id', $this->tenantId)
            ->exists();
        abort_unless($isMember, 403);
        app(TenantContext::class)->set(Tenant::query()->findOrFail($this->tenantId));
    }

    #[Computed]
    public function catalogs(): \Illuminate\Database\Eloquent\Collection
    {
        return Catalog::query()->with('category:id,name')
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->when($this->filterCategory !== null, fn ($q) => $q->where('category_id', $this->filterCategory))
            ->orderBy('name')->get();
    }

    #[Computed]
    public function categories(): \Illuminate\Database\Eloquent\Collection
    {
        return CatalogCategory::query()->orderBy('name')->get();
    }

    public function addCategory(): void
    {
        $data = $this->validate(['newCategory' => ['required', 'string', 'max:120']]);
        CatalogCategory::create(['name' => $data['newCategory']]);
        $this->newCategory = '';
        unset($this->categories);
    }

    public function createCatalog(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'categoryId' => ['required', 'integer'],
            'basePrice' => ['required', 'integer', 'min:0'],
            'prepMinutes' => ['required', 'integer', 'min:0', 'max:240'],
            'stockQty' => ['required', 'integer', 'min:0'],
        ]);

        $category = CatalogCategory::query()->findOrFail($data['categoryId']);
        $catalog = new Catalog;
        $catalog->forceFill([
            'category_id' => $category->id,
            'name' => $data['name'],
            'base_price' => $data['basePrice'],
            'prep_minutes' => $data['prepMinutes'],
            'stock_qty' => $data['stockQty'],
            'is_available' => true,
        ]);
        $catalog->save();

        $this->reset(['name', 'basePrice', 'prepMinutes', 'stockQty', 'categoryId']);
        unset($this->catalogs);
        session()->flash('status', 'Item katalog berhasil dibuat.');
    }

    public function toggle(int $catalogId): void
    {
        $catalog = Catalog::query()->findOrFail($catalogId);
        app(CatalogStockService::class)->toggleAvailability($catalog);
        unset($this->catalogs);
    }

    public function restock(int $catalogId, int $delta): void
    {
        $catalog = Catalog::query()->findOrFail($catalogId);
        app(CatalogStockService::class)->adjust($catalog, $delta, 'restock', (string) Str::uuid(), 'manual restock');
        unset($this->catalogs);
    }
};
?>

<div class="space-y-6">
    @if (session('status'))
        <div class="rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800 dark:bg-green-900/40 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    {{-- Kategori --}}
    <section class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
        <h2 class="font-semibold text-lg">Kategori Katalog</h2>
        <div class="mt-2 flex flex-wrap items-end gap-2">
            <x-input name="newCategory" label="Kategori Baru" wire:model="newCategory" />
            <x-button type="button" wire:click="addCategory">Tambah Kategori</x-button>
        </div>
        <p class="mt-2 text-sm text-zinc-500">{{ $this->categories->pluck('name')->join(', ') ?: 'Belum ada kategori.' }}</p>
    </section>

    {{-- Form Tambah Item --}}
    <section class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
        <h2 class="font-semibold text-lg">Tambah Item Katalog</h2>
        <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <x-input name="name" label="Nama Item" wire:model="name" />
            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Kategori</label>
                <select wire:model="categoryId" class="min-h-11 rounded-lg border border-zinc-300 bg-white px-3 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                    <option value="">Pilih Kategori</option>
                    @foreach ($this->categories as $category)
                        <option value="{{ $category->id }}" wire:key="cat-{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <x-input name="basePrice" label="Harga (Rp)" type="number" wire:model="basePrice" />
            <x-input name="stockQty" label="Stok" type="number" wire:model="stockQty" />
        </div>
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        @error('categoryId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        <x-button type="button" wire:click="createCatalog" class="mt-4">Simpan Item</x-button>
    </section>

    {{-- Tabel Daftar Item & Stok --}}
    <section>
        <div class="flex flex-wrap items-end justify-between gap-2">
            <h2 class="font-semibold text-lg">Daftar Item & Stok</h2>
            <div class="flex flex-wrap gap-2">
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari item…"
                    class="min-h-11 rounded-lg border border-zinc-300 bg-white px-3 text-sm dark:border-zinc-600 dark:bg-zinc-800" />
                <select wire:model.live="filterCategory" class="min-h-11 rounded-lg border border-zinc-300 bg-white px-3 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                    <option value="">Kategori: Semua</option>
                    @foreach ($this->categories as $category)
                        <option value="{{ $category->id }}" wire:key="fcat-{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-2 overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
            <table class="w-full min-w-[40rem] text-left text-sm">
                <thead class="bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                    <tr>
                        <th class="px-4 py-2">Nama</th>
                        <th class="px-4 py-2">Kategori</th>
                        <th class="px-4 py-2">Harga</th>
                        <th class="px-4 py-2">Stok</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->catalogs as $item)
                        <tr wire:key="catalog-{{ $item->id }}" class="border-t border-zinc-200 dark:border-zinc-800">
                            <td class="px-4 py-3 font-medium">{{ $item->name }}</td>
                            <td class="px-4 py-3">{{ $item->category?->name }}</td>
                            <td class="px-4 py-3">Rp {{ number_format($item->base_price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 {{ $item->stock_qty <= 5 ? 'font-semibold text-amber-600' : '' }}">{{ $item->stock_qty }}</td>
                            <td class="px-4 py-3">
                                <x-status-badge :status="$item->is_available ? 'active' : 'suspended'">
                                    {{ $item->is_available ? 'Tersedia' : 'Habis' }}
                                </x-status-badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" wire:click="toggle({{ $item->id }})" class="me-2 text-sm underline">
                                    {{ $item->is_available ? 'Tandai Habis' : 'Aktifkan' }}
                                </button>
                                <button type="button" wire:click="restock({{ $item->id }}, 10)" class="text-sm underline">+10 Stok</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-3" colspan="6"><x-empty-state title="Belum ada item katalog" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>