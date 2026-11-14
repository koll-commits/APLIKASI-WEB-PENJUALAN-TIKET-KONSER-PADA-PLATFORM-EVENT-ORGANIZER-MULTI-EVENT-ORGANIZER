<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;
use App\Models\UserPlatformRole;

/**
 * Keputusan administratif dipusatkan di policy (bukan string role tersebar).
 * Aktor harus mengelola event milik tenant terkait.
 */
class TenantPolicy
{
    private function managesEvent(User $user, ?int $eventId = null): bool
    {
        $query = UserPlatformRole::query()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'manager', 'finance', 'admin']);

        if ($eventId !== null) {
            $query->where('event_id', $eventId);
        }

        return $query->exists();
    }

    public function viewAny(User $user): bool
    {
        return UserPlatformRole::query()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return UserPlatformRole::query()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'manager', 'admin'])
            ->exists();
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $this->managesEvent($user, $tenant->event_id);
    }

    public function scheduleCommission(User $user, Tenant $tenant): bool
    {
        return $this->managesEvent($user, $tenant->event_id);
    }

    public function manageBank(User $user, Tenant $tenant): bool
    {
        return $this->managesEvent($user, $tenant->event_id);
    }

    public function assignRole(User $user, Tenant $tenant): bool
    {
        return $this->managesEvent($user, $tenant->event_id);
    }
}
