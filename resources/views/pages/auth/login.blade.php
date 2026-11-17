<x-layouts::auth title="Masuk">
    <div class="flex flex-col gap-6">
        <x-auth-header title="Masuk ke StageTix" description="Pilih akun role di bawah atau masukkan surel manual." />

        <!-- Pilihan Cepat Akun Demo (Role Selector) -->
        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900/50">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-zinc-500">Pilih Cepat Role Akun:</p>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                <!-- 1. Pelanggan -->
                <button type="button" onclick="selectRole('test@example.com', 'password')"
                        class="flex flex-col items-start rounded-lg border border-zinc-200 bg-white p-2.5 text-left transition hover:border-red-500 hover:bg-red-50/50 dark:border-zinc-700 dark:bg-zinc-800">
                    <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">👤 Pelanggan</span>
                    <span class="text-[10px] text-zinc-500 truncate w-full">test@example.com</span>
                </button>

                <!-- 2. EO / Tenant -->
                <button type="button" onclick="selectRole('eo@stagetix.test', 'password')"
                        class="flex flex-col items-start rounded-lg border border-zinc-200 bg-white p-2.5 text-left transition hover:border-red-500 hover:bg-red-50/50 dark:border-zinc-700 dark:bg-zinc-800">
                    <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">🎪 EO (Tenant)</span>
                    <span class="text-[10px] text-zinc-500 truncate w-full">eo@stagetix.test</span>
                </button>

                <!-- 3. Admin -->
                <button type="button" onclick="selectRole('admin@stagetix.test', 'password')"
                        class="flex flex-col items-start rounded-lg border border-zinc-200 bg-white p-2.5 text-left transition hover:border-red-500 hover:bg-red-50/50 dark:border-zinc-700 dark:bg-zinc-800">
                    <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">🛡️ Admin</span>
                    <span class="text-[10px] text-zinc-500 truncate w-full">admin@stagetix.test</span>
                </button>
            </div>
            <p id="roleNote" class="mt-2 text-[11px] text-zinc-500 italic"></p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-passkey-verify />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6" id="loginForm">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                id="emailField"
                label="Surel"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="nama@stagetix.test"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    id="passwordField"
                    label="Kata sandi"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="Kata sandi"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        Lupa kata sandi?
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" label="Ingat saya" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    Masuk Sekarang →
                </flux:button>
            </div>
        </form>
    </div>

    <script>
    function selectRole(email, password) {
        const emailInput = document.querySelector('input[name="email"]');
        const passInput = document.querySelector('input[name="password"]');
        const note = document.getElementById('roleNote');

        if (emailInput && passInput) {
            emailInput.value = email;
            passInput.value = password;
            emailInput.dispatchEvent(new Event('input', { bubbles: true }));
            passInput.dispatchEvent(new Event('input', { bubbles: true }));

            note.textContent = '✓ Akun ' + email + ' terpilih. Klik "Masuk Sekarang" untuk melanjutkan.';
        }
    }
    </script>
</x-layouts::auth>