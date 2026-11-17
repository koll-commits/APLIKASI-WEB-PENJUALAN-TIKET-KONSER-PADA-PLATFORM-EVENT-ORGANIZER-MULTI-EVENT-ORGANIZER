<x-layouts.tenant title="Kelola Katalog — {{ $tenant->display_name }}">
    <h1 class="mb-4 text-xl font-semibold">Kelola Katalog & Stok</h1>
    <livewire:catalog::catalog-manager :tenant-id="$tenant->id" />
</x-layouts.tenant>