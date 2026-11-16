<x-layouts.tenant title="Event">
    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-2 text-sm text-green-800 dark:bg-green-900/40 dark:text-green-300">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-100 px-4 py-2 text-sm text-red-800 dark:bg-red-900/40 dark:text-red-300">{{ $errors->first() }}</div>
    @endif
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Event Saya</h1>
        @can('create', \App\Models\Event::class)
            <a href="{{ route('tenant.concerts.create', ['tenant' => $tenant->slug]) }}"><x-button>+ Tambah event</x-button></a>
        @endcan
    </div>
    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
        <table class="w-full min-w-[44rem] text-left text-sm">
            <thead class="bg-zinc-100 text-xs uppercase tracking-wide text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                <tr>
                    <th class="px-4 py-2">Event</th>
                    <th class="px-4 py-2">Lokasi</th>
                    <th class="px-4 py-2">Jadwal</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($concerts as $concert)
                    <tr @class(['border-t border-zinc-200 dark:border-zinc-800', 'text-zinc-400' => $concert->status === 'cancelled'])>
                        <td class="px-4 py-3 font-medium">{{ $concert->name }}</td>
                        <td class="px-4 py-3">{{ $concert->venue }}, {{ $concert->city }}</td>
                        <td class="whitespace-nowrap px-4 py-3">{{ $concert->starts_at?->translatedFormat('j M Y, H:i') }}</td>
                        <td class="px-4 py-3">
                            <x-status-badge :status="match ($concert->status) { 'published' => 'active', 'draft' => 'pending', default => 'suspended' }">{{ \App\Models\Event::STATUSES[$concert->status] ?? $concert->status }}</x-status-badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @can('update', $concert)
                                <div class="flex items-center justify-end gap-3">
                                    <a class="text-sm font-medium underline" href="{{ route('tenant.concerts.edit', ['tenant' => $tenant->slug, 'concert' => $concert->id]) }}">Ubah</a>
                                    @if ($concert->status === 'draft')
                                        <form method="POST" action="{{ route('tenant.concerts.destroy', ['tenant' => $tenant->slug, 'concert' => $concert->id]) }}"
                                              onsubmit="return confirm('Hapus event {{ e($concert->name) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="min-h-11 text-sm font-medium text-red-700 hover:underline dark:text-red-400">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-3" colspan="5"><x-empty-state title="Belum ada event" description="Buat event pertama Anda, lalu tambahkan jenis tiketnya." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.tenant>
