<x-layouts.tenant title="Ubah Event">
    <div class="mb-6 flex items-center gap-3">
        <h1 class="text-xl font-semibold">{{ $concert->name }}</h1>
        <x-status-badge :status="match ($concert->status) { 'published' => 'active', 'draft' => 'pending', default => 'suspended' }">{{ \App\Models\Event::STATUSES[$concert->status] ?? $concert->status }}</x-status-badge>
    </div>
    @include('ticketing::concerts._form', ['action' => route('tenant.concerts.update', ['tenant' => $tenant->slug, 'concert' => $concert->id]), 'concert' => $concert])
</x-layouts.tenant>
