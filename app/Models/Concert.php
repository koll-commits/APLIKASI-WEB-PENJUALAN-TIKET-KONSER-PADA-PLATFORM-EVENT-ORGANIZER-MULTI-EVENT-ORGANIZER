<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\ConcertFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Event/konser milik satu EO. tenant_id diisi otomatis dari TenantContext (tidak mass-assignable).
 */
class Concert extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<ConcertFactory> */
    use HasFactory;

    public const STATUSES = ['draft' => 'Draf', 'published' => 'Terbit', 'cancelled' => 'Dibatalkan'];

    protected $fillable = ['name', 'slug', 'venue', 'city', 'starts_at', 'ends_at', 'description', 'status'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
