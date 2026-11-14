<?php

namespace App\Modules\Admin\Http\Requests;

use App\Models\Event;
use App\Models\Tenant;
use App\Models\UserPlatformRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Tenant::class) ?? false;
    }

    /** Event berasal dari keanggotaan aktor (tepercaya), bukan input. */
    public function eventId(): ?int
    {
        $value = UserPlatformRole::query()
            ->where('user_id', $this->user()?->id)
            ->whereIn('role', ['owner', 'manager', 'admin'])
            ->value('event_id');

        if ($value !== null) {
            return (int) $value;
        }

        if ($this->user()?->isAdmin()) {
            return (int) Event::query()->value('id');
        }

        return null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $eventId = $this->eventId();

        return [
            'code' => ['required', 'alpha_dash', 'max:30',
                Rule::unique('tenants', 'code')->where(fn ($q) => $q->where('event_id', $eventId))],
            'slug' => ['required', 'alpha_dash', 'max:100',
                Rule::unique('tenants', 'slug')->where(fn ($q) => $q->where('event_id', $eventId))],
            'display_name' => ['required', 'string', 'max:120'],
            'commission_rate' => ['required', 'numeric', 'between:0,100'],
            // UC-21 langkah 3: penanggung jawab (akun owner awal) + rekening bank tenant.
            'pic_name' => ['required', 'string', 'max:120'],
            'pic_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'bank_code' => ['required', 'string', 'max:20'],
            'account_holder' => ['required', 'string', 'max:120'],
            'account_number' => ['required', 'digits_between:6,20'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'display_name' => 'nama tampilan',
            'code' => 'kode',
            'commission_rate' => 'komisi',
            'pic_name' => 'nama penanggung jawab',
            'pic_email' => 'surel penanggung jawab',
            'bank_code' => 'bank',
            'account_holder' => 'nama pemilik rekening',
            'account_number' => 'nomor rekening',
        ];
    }
}
