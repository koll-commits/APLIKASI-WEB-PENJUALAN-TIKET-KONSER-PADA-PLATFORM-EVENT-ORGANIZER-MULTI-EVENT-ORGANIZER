<?php

namespace App\Policies;

use App\Models\Catalog;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

/**
 * Policy = lapisan kemampuan. Binding membatasi objek; policy membatasi aksi.
 * Defense-in-depth: menu harus milik tenant aktif (selain global scope + composite FK).
 */
class CatalogPolicy
{
    private function ownedByActiveTenant(Catalog $catalog): bool
    {
        return $catalog->tenant_id === app(TenantContext::class)->idOrNull();
    }

    public function view(User $user, Catalog $catalog): bool
    {
        return $this->ownedByActiveTenant($catalog);
    }

    public function update(User $user, Catalog $catalog): bool
    {
        return $this->ownedByActiveTenant($catalog);
    }

    public function delete(User $user, Catalog $catalog): bool
    {
        return $this->ownedByActiveTenant($catalog);
    }
}
