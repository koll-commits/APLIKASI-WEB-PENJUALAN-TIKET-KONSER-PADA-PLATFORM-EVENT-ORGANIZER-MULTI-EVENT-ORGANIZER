<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Peran user tingkat platform global (Super Admin / Admin StageTix).
 */
class UserPlatformRole extends Model
{
    protected $fillable = ['user_id', 'role'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}