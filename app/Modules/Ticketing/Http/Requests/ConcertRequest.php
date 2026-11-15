<?php

namespace App\Modules\Ticketing\Http\Requests;

use App\Models\Concert;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validasi tambah/ubah event EO. Otorisasi lewat ConcertPolicy (owner/operator). */
class ConcertRequest extends FormRequest
{
    public function authorize(): bool
    {
        $concert = $this->route('concert');

        return $concert instanceof Concert
            ? ($this->user()?->can('update', $concert) ?? false)
            : ($this->user()?->can('create', Concert::class) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'venue' => ['required', 'string', 'max:150'],
            'city' => ['required', 'string', 'max:80'],
            // Event baru harus di masa mendatang; saat mengubah, tanggal lama tidak dipaksa.
            'starts_at' => ['required', 'date', $this->isMethod('POST') ? 'after:now' : 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(array_keys(Concert::STATUSES))],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => 'nama event',
            'venue' => 'lokasi/venue',
            'city' => 'kota',
            'starts_at' => 'waktu mulai',
            'ends_at' => 'waktu selesai',
            'description' => 'deskripsi',
            'status' => 'status',
        ];
    }
}
