<x-layouts.tenant title="Tambah Event">
    <h1 class="mb-6 text-xl font-semibold">Tambah Event</h1>
    @include('ticketing::concerts._form', ['action' => route('tenant.concerts.store', ['tenant' => $tenant->slug])])
</x-layouts.tenant>
