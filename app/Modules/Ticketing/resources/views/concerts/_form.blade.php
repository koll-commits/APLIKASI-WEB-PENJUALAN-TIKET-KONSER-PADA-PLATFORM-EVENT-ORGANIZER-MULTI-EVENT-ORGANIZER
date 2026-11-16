@php $c = $concert ?? null; @endphp
@if ($errors->any())
    <div class="mb-4 rounded-lg bg-red-100 px-4 py-2 text-sm text-red-800 dark:bg-red-900/40 dark:text-red-300">
        <ul class="list-disc ps-4">@foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
    </div>
@endif
<form method="POST" action="{{ $action }}" class="grid max-w-3xl gap-6 md:grid-cols-2">
    @csrf
    @if ($c) @method('PATCH') @endif
    <fieldset class="space-y-4">
        <legend class="mb-2 text-sm font-semibold uppercase tracking-wide text-zinc-500">Informasi event</legend>
        <x-input name="name" label="Nama event / konser" value="{{ old('name', $c?->name) }}" required />
        <x-input name="venue" label="Lokasi / venue" value="{{ old('venue', $c?->venue) }}" required />
        <x-input name="city" label="Kota" value="{{ old('city', $c?->city) }}" required />
        <div class="flex flex-col gap-1">
            <label for="description" class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Deskripsi</label>
            <textarea id="description" name="description" rows="5"
                class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">{{ old('description', $c?->description) }}</textarea>
        </div>
    </fieldset>
    <fieldset class="space-y-4">
        <legend class="mb-2 text-sm font-semibold uppercase tracking-wide text-zinc-500">Jadwal &amp; status</legend>
        <x-input name="starts_at" label="Waktu mulai" type="datetime-local" value="{{ old('starts_at', $c?->starts_at?->format('Y-m-d\TH:i')) }}" required />
        <x-input name="ends_at" label="Waktu selesai (opsional)" type="datetime-local" value="{{ old('ends_at', $c?->ends_at?->format('Y-m-d\TH:i')) }}" />
        <div class="flex flex-col gap-1">
            <label for="status" class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Status</label>
            <select id="status" name="status" class="min-h-11 rounded-lg border border-zinc-300 bg-white px-3 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                @foreach (\App\Models\Event::STATUSES as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $c?->status ?? 'draft') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="text-xs text-zinc-500">Draf tidak tampil ke pembeli. Terbit tampil di katalog event.</p>
        </div>
    </fieldset>
    <div class="flex gap-2 md:col-span-2">
        <x-button type="submit">Simpan</x-button>
        <a href="{{ route('tenant.concerts.index', ['tenant' => $tenant->slug]) }}"><x-button variant="secondary">Batal</x-button></a>
    </div>
</form>
