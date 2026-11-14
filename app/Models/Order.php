<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Order induk tingkat platform (satu sesi checkout penonton untuk sebuah event).
 */
class Order extends Model
{
    protected $fillable = ['order_number', 'checkout_key', 'status'];

    protected function casts(): array
    {
        return [
            'public_id' => 'string',
            'subtotal_amount' => 'integer',
            'tax_amount' => 'integer',
            'service_fee_amount' => 'integer',
            'grand_total_amount' => 'integer',
            'customer_snapshot' => 'array',
            'table_snapshot' => 'array',
            'seat_snapshot' => 'array',
            'placed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Event, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** @return HasMany<TenantOrder, $this> */
    public function tenantOrders(): HasMany
    {
        return $this->hasMany(TenantOrder::class);
    }
}
