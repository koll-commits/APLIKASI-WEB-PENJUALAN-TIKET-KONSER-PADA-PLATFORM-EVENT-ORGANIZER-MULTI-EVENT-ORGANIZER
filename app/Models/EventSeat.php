<?php

namespace App\Models;

use Database\Factories\EventSeatFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSeat extends Model
{
    /** @use HasFactory<EventSeatFactory> */
    use HasFactory;

    protected $fillable = ['event_id', 'code', 'label', 'zone', 'status'];

    /** @return BelongsTo<Event, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}