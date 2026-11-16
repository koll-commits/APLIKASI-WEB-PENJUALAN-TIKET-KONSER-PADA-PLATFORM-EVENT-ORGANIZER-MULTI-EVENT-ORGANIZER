<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    public const STATUSES = [
        'draft' => 'Draf',
        'published' => 'Terbit',
        'cancelled' => 'Dibatalkan',
        'active' => 'Aktif',
        'inactive' => 'Nonaktif',
    ];

    protected $fillable = [
        'tenant_id',
        'code',
        'slug',
        'name',
        'venue',
        'city',
        'starts_at',
        'ends_at',
        'description',
        'tax_rate',
        'service_fee_rate',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'tax_rate' => 'decimal:4',
            'service_fee_rate' => 'decimal:4',
        ];
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** @return HasMany<Tenant, $this> */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    /** @return HasMany<EventSeat, $this> */
    public function eventSeats(): HasMany
    {
        return $this->hasMany(EventSeat::class);
    }
}   