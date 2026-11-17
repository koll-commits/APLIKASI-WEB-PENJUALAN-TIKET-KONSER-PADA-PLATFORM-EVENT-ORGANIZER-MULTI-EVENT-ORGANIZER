<x-layouts::auth title="Daftar">
    <div class="flex flex-col items-center">
        <!-- Logo Tiket -->
        <div class="mb-3 flex justify-center text-zinc-800">
            <svg class="w-16 h-12" viewBox="0 0 64 40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="2" width="60" height="36" rx="6" />
                <path d="M2 16 A 4 4 0 0 1 2 24" fill="#fff" />
                <path d="M62 16 A 4 4 0 0 0 62 24" fill="#fff" />
                <line x1="46" y1="2" x2="46" y2="38" stroke-dasharray="3 3" />
            </svg>
        </div>

        <!-- Nama Brand -->
        <h1 class="text-3xl font-extrabold tracking-tight text-zinc-900">
            Stage<span class="text-zinc-950 font-black">TIX</span>
        </h1>

        <!-- Judul Sambutan -->
        <h2 class="mt-4 text-base font-bold text-zinc-900 text-center">
            Buat Akun StageTix Kamu !
        </h2>

        <!-- Status Notifikasi / Sesi -->
        @if (session('status'))
            <div class="mt-3 text-xs text-center text-green-600 font-medium">
                {{ session('status') }}
            </div>
        @endif

        <!-- Form Registrasi -->
        <form method="POST" action="{{ route('register.store') }}" class="w-full mt-6 flex flex-col gap-3">
            @csrf

            <!-- Sub Label -->
            <p class="text-xs font-semibold text-zinc-700">Daftar!</p>

            <!-- Input Username / Nama -->
            <div>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="username"
                    class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-zinc-300 bg-white text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-[#0c3248] transition"
                />
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Input Password dengan Toggle Mata -->
            <div>
                <div class="relative">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        autocomplete="new-password"
                        placeholder="password"
                        class="w-full pl-3.5 pr-10 py-2.5 text-sm rounded-lg border border-zinc-300 bg-white text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-[#0c3248] transition"
                    />
                    <button
                        type="button"
                        onclick="togglePassword('password', 'eye-icon-pass', 'eye-off-icon-pass')"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-zinc-400 hover:text-zinc-600 focus:outline-none"
                        tabindex="-1"
                        aria-label="Tampilkan kata sandi"
                    >
                        <!-- Ikon Mata Terbuka -->
                        <svg id="eye-icon-pass" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <!-- Ikon Mata Tertutup -->
                        <svg id="eye-off-icon-pass" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Input Konfirmasi Password (Re-password) dengan Toggle Mata -->
            <div>
                <div class="relative">
                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Re-password"
                        class="w-full pl-3.5 pr-10 py-2.5 text-sm rounded-lg border border-zinc-300 bg-white text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-[#0c3248] transition"
                    />
                    <button
                        type="button"
                        onclick="togglePassword('password_confirmation', 'eye-icon-confirm', 'eye-off-icon-confirm')"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-zinc-400 hover:text-zinc-600 focus:outline-none"
                        tabindex="-1"
                        aria-label="Tampilkan konfirmasi kata sandi"
                    >
                        <svg id="eye-icon-confirm" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg id="eye-off-icon-confirm" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                @error('password_confirmation')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Input Email -->
            <div>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                    placeholder="Email"
                    class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-zinc-300 bg-white text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-[#0c3248] transition"
                />
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tombol Submit Navy -->
            <div class="flex justify-center mt-3">
                <button
                    type="submit"
                    data-test="register-user-button"
                    class="px-8 py-2.5 rounded-lg bg-[#0c3248] hover:bg-[#082232] text-white text-sm font-semibold transition duration-150 shadow"
                >
                    Daftar Sekarang
                </button>
            </div>

            <!-- Link Kembali ke Login -->
            <p class="text-xs text-center text-zinc-500 mt-4">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-[#0c3248] font-semibold hover:underline">
                    Masuk
                </a>
            </p>
        </form>
    </div>

    <!-- Hilangkan tombol mata bawaan browser agar tidak bertumpuk -->
    <style>
        input::-ms-reveal,
        input::-ms-clear {
            display: none;
        }
    </style>

    <!-- Script Toggle Show/Hide Password -->
    <script>
        function togglePassword(inputId, openIconId, closeIconId) {
            const input = document.getElementById(inputId);
            const openIcon = document.getElementById(openIconId);
            const closeIcon = document.getElementById(closeIconId);

            if (input.type === 'password') {
                input.type = 'text';
                openIcon.classList.add('hidden');
                closeIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                openIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
            }
        }
    </script>
</x-layouts::auth>