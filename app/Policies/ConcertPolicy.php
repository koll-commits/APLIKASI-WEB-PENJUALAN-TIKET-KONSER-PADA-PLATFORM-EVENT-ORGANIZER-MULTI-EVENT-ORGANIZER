<?php

namespace App\Policies;

use App\Models\Concert;
use App\Models\Event;
use App\Models\User;
use App\Models\UserTenantRole;
use App\Support\Tenancy\TenantContext;

class ConcertPolicy
{
    private function roleInActiveTenant(User $user, array $roles): bool
    {
        $tenantId = app(TenantContext::class)->idOrNull();
        if ($tenantId === null) {
            return false;
        }

        return UserTenantRole::query()
            ->where('user_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->whereIn('role', $roles)
            ->exists();
    }

    private function ownedByActiveTenant(Event|Concert $concert): bool
    {
        return $concert->tenant_id === app(TenantContext::class)->idOrNull();
    }

    public function viewAny(User $user): bool
    {
        return $this->roleInActiveTenant($user, ['owner', 'operator', 'cashier']);
    }

    public function view(User $user, Event|Concert $concert): bool
    {
        return $this->ownedByActiveTenant($concert) && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->roleInActiveTenant($user, ['owner', 'operator']);
    }

    public function update(User $user, Event|Concert $concert): bool
    {
        return $this->ownedByActiveTenant($concert) && $this->create($user);
    }

    public function delete(User $user, Event|Concert $concert): bool
    {
        return $this->update($user, $concert);
    }
}