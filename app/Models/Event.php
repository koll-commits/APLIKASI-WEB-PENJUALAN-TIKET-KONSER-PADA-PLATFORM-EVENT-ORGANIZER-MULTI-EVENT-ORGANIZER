<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected $fillable = ['code', 'slug', 'name', 'tax_rate', 'service_fee_rate', 'status'];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:4',
            'service_fee_rate' => 'decimal:4',
        ];
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