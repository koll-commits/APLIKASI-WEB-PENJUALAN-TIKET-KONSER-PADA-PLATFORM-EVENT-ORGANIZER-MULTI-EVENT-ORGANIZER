<?php

namespace App\Policies;

use App\Models\Concert;
use App\Models\User;
use App\Models\UserTenantRole;
use App\Support\Tenancy\TenantContext;

/**
 * Policy event/konser EO: objek harus milik tenant aktif, dan aksi tulis hanya untuk
 * owner/operator (kasir hanya boleh melihat).
 */
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

    private function ownedByActiveTenant(Concert $concert): bool
    {
        return $concert->tenant_id === app(TenantContext::class)->idOrNull();
    }

    public function viewAny(User $user): bool
    {
        return $this->roleInActiveTenant($user, ['owner', 'operator', 'cashier']);
    }

    public function view(User $user, Concert $concert): bool
    {
        return $this->ownedByActiveTenant($concert) && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->roleInActiveTenant($user, ['owner', 'operator']);
    }

    public function update(User $user, Concert $concert): bool
    {
        return $this->ownedByActiveTenant($concert) && $this->create($user);
    }

    public function delete(User $user, Concert $concert): bool
    {
        return $this->update($user, $concert);
    }
}
