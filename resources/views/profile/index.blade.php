<x-layouts.app title="Profil & Ganti Password">

    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Profil & Pengaturan Akun
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola informasi akun dan perbarui kata sandi Anda dengan aman.
            </p>
        </div>

        {{-- Role Badge --}}
        <div>
            @if($user->role === 'admin')
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 border border-indigo-200 shadow-sm">
                <i class="fas fa-user-shield text-indigo-500"></i> Role: Administrator
            </span>
            @elseif($user->role === 'dokter')
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 border border-purple-200 shadow-sm">
                <i class="fas fa-user-doctor text-purple-500"></i> Role: Dokter
            </span>
            @else
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200 shadow-sm">
                <i class="fas fa-bed-pulse text-amber-600"></i> Role: Pasien
            </span>
            @endif
        </div>
    </div>

    {{-- Success Alert (if redirected with success) --}}
    @if(session('success'))
    <div class="alert alert-success bg-emerald-50 text-emerald-800 border border-emerald-200 mb-6 rounded-2xl shadow-sm flex items-center gap-3 p-4">
        <i class="fas fa-circle-check text-emerald-500 text-xl"></i>
        <div>
            <div class="font-semibold text-sm">{{ session('success') }}</div>
            <div class="text-xs text-emerald-600">Kata sandi akun Anda telah berhasil diganti dan disimpan dengan aman.</div>
        </div>
    </div>
    @endif

    {{-- Layout Grid: Profile Info (Left) & Ganti Password Form (Right) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Kolom Kiri: Ringkasan Profil --}}
        <div class="lg:col-span-4 space-y-6">
            <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200 overflow-hidden">
                {{-- Banner Header --}}
                <div class="h-24 bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600"></div>

                <div class="card-body p-6 pt-0 relative">
                    {{-- Avatar --}}
                    <div class="-mt-12 mb-4 flex justify-between items-end">
                        <div class="w-20 h-20 rounded-2xl bg-white p-1 shadow-lg ring-4 ring-white">
                            <div class="w-full h-full rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-2xl shadow-inner">
                                {{ strtoupper(substr($user->nama ?? $user->name ?? 'U', 0, 1)) }}
                            </div>
                        </div>
                    </div>

                    {{-- Nama & Email --}}
                    <h2 class="text-xl font-bold text-slate-800">
                        {{ $user->nama ?? $user->name ?? 'Pengguna' }}
                    </h2>
                    <p class="text-xs text-slate-500 flex items-center gap-1.5 mt-0.5">
                        <i class="fas fa-envelope text-slate-400"></i>
                        {{ $user->email }}
                    </p>

                    <div class="divider my-3"></div>

                    {{-- Detail Info Tambahan --}}
                    <div class="space-y-3 text-sm">
                        @if($user->role === 'dokter')
                        <div class="flex items-center justify-between text-xs py-1 border-b border-slate-100">
                            <span class="text-slate-500 flex items-center gap-1.5">
                                <i class="fas fa-hospital text-purple-400"></i> Poli
                            </span>
                            <span class="font-semibold text-slate-700">
                                {{ $user->poli->nama_poli ?? 'Tidak ada poli' }}
                            </span>
                        </div>
                        @endif

                        @if($user->no_rm)
                        <div class="flex items-center justify-between text-xs py-1 border-b border-slate-100">
                            <span class="text-slate-500 flex items-center gap-1.5">
                                <i class="fas fa-id-card-clip text-amber-400"></i> No. Rekam Medis
                            </span>
                            <span class="font-mono font-semibold text-slate-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                {{ $user->no_rm }}
                            </span>
                        </div>
                        @endif

                        @if($user->no_ktp)
                        <div class="flex items-center justify-between text-xs py-1 border-b border-slate-100">
                            <span class="text-slate-500 flex items-center gap-1.5">
                                <i class="fas fa-address-card text-slate-400"></i> No. KTP
                            </span>
                            <span class="font-mono text-slate-700">{{ $user->no_ktp }}</span>
                        </div>
                        @endif

                        @if($user->no_hp)
                        <div class="flex items-center justify-between text-xs py-1 border-b border-slate-100">
                            <span class="text-slate-500 flex items-center gap-1.5">
                                <i class="fas fa-phone text-slate-400"></i> No. HP
                            </span>
                            <span class="text-slate-700">{{ $user->no_hp }}</span>
                        </div>
                        @endif

                        @if($user->alamat)
                        <div class="text-xs pt-1">
                            <span class="text-slate-500 flex items-center gap-1.5 mb-1">
                                <i class="fas fa-location-dot text-slate-400"></i> Alamat
                            </span>
                            <p class="text-slate-700 bg-slate-50 p-2.5 rounded-lg border border-slate-100 leading-relaxed">
                                {{ $user->alamat }}
                            </p>
                        </div>
                        @endif
                    </div>

                    {{-- Security Badge --}}
                    <div class="mt-4 p-3 bg-indigo-50/60 rounded-xl border border-indigo-100 text-xs text-indigo-700 flex items-center gap-2">
                        <i class="fas fa-lock text-indigo-500"></i>
                        <span>Akun terlindungi dengan enkripsi kata sandi Hash (Bcrypt).</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Form Ganti Password --}}
        <div class="lg:col-span-8">
            <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
                <div class="card-body p-6 sm:p-8">

                    {{-- Card Header --}}
                    <div class="flex items-center gap-3 pb-4 mb-6 border-b border-slate-200">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                            <i class="fas fa-key text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-800">
                                Ganti Password
                            </h2>
                            <p class="text-xs text-slate-500">
                                Ubah password akun Anda. Pastikan password baru aman dan tidak mudah ditebak.
                            </p>
                        </div>
                    </div>

                    {{-- Form Ganti Password --}}
                    <form action="{{ route('profile.password.update') }}" method="POST" id="formGantiPassword" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="space-y-5">

                            {{-- 1. Password Lama --}}
                            <div class="form-control">
                                <label for="current_password" class="label pb-1.5">
                                    <span class="label-text font-semibold text-slate-700 text-sm">
                                        Password Lama <span class="text-red-500">*</span>
                                    </span>
                                </label>
                                <div class="relative">
                                    <input
                                        type="password"
                                        id="current_password"
                                        name="current_password"
                                        placeholder="Masukkan password akun yang sedang login..."
                                        class="input input-bordered w-full rounded-xl pr-11 border-2 text-sm transition
                                               focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary
                                               @error('current_password') input-error border-red-500 bg-red-50/20 @enderror"
                                        required
                                        autocomplete="current-password"
                                    >
                                    <button
                                        type="button"
                                        onclick="togglePasswordVisibility('current_password', 'current_password_icon')"
                                        class="absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400 hover:text-slate-600 transition focus:outline-none"
                                        title="Tampilkan / Sembunyikan Password"
                                        tabindex="-1"
                                    >
                                        <i id="current_password_icon" class="fas fa-eye text-sm"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1 font-medium">
                                    <i class="fas fa-circle-exclamation"></i>
                                    <span>{{ $message }}</span>
                                </p>
                                @enderror
                            </div>

                            {{-- 2. Password Baru --}}
                            <div class="form-control">
                                <label for="password" class="label pb-1.5">
                                    <span class="label-text font-semibold text-slate-700 text-sm">
                                        Password Baru <span class="text-red-500">*</span>
                                    </span>
                                </label>
                                <div class="relative">
                                    <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        placeholder="Minimal 8 karakter..."
                                        class="input input-bordered w-full rounded-xl pr-11 border-2 text-sm transition
                                               focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary
                                               @error('password') input-error border-red-500 bg-red-50/20 @enderror"
                                        required
                                        autocomplete="new-password"
                                    >
                                    <button
                                        type="button"
                                        onclick="togglePasswordVisibility('password', 'password_icon')"
                                        class="absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400 hover:text-slate-600 transition focus:outline-none"
                                        title="Tampilkan / Sembunyikan Password"
                                        tabindex="-1"
                                    >
                                        <i id="password_icon" class="fas fa-eye text-sm"></i>
                                    </button>
                                </div>
                                @error('password')
                                <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1 font-medium">
                                    <i class="fas fa-circle-exclamation"></i>
                                    <span>{{ $message }}</span>
                                </p>
                                @else
                                <p class="text-xs text-slate-400 mt-1">
                                    Minimal 8 karakter dan tidak boleh sama dengan password lama.
                                </p>
                                @enderror
                            </div>

                            {{-- 3. Konfirmasi Password Baru --}}
                            <div class="form-control">
                                <label for="password_confirmation" class="label pb-1.5">
                                    <span class="label-text font-semibold text-slate-700 text-sm">
                                        Konfirmasi Password Baru <span class="text-red-500">*</span>
                                    </span>
                                </label>
                                <div class="relative">
                                    <input
                                        type="password"
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        placeholder="Ketik ulang password baru Anda..."
                                        class="input input-bordered w-full rounded-xl pr-11 border-2 text-sm transition
                                               focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary
                                               @error('password_confirmation') input-error border-red-500 bg-red-50/20 @enderror"
                                        required
                                        autocomplete="new-password"
                                    >
                                    <button
                                        type="button"
                                        onclick="togglePasswordVisibility('password_confirmation', 'password_confirmation_icon')"
                                        class="absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400 hover:text-slate-600 transition focus:outline-none"
                                        title="Tampilkan / Sembunyikan Password"
                                        tabindex="-1"
                                    >
                                        <i id="password_confirmation_icon" class="fas fa-eye text-sm"></i>
                                    </button>
                                </div>
                                @error('password_confirmation')
                                <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1 font-medium">
                                    <i class="fas fa-circle-exclamation"></i>
                                    <span>{{ $message }}</span>
                                </p>
                                @enderror
                            </div>

                        </div>

                        {{-- Panduan Keamanan Password --}}
                        <div class="mt-6 p-4 rounded-xl bg-slate-50 border border-slate-200">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-2 flex items-center gap-1.5">
                                <i class="fas fa-shield-check text-indigo-500"></i> Ketentuan Password:
                            </h3>
                            <ul class="text-xs text-slate-500 space-y-1 list-disc list-inside">
                                <li>Password lama wajib cocok dengan password akun Anda saat ini.</li>
                                <li>Password baru minimal berjumlah <strong>8 karakter</strong>.</li>
                                <li>Password baru tidak boleh sama dengan password lama.</li>
                                <li>Kolom konfirmasi password baru harus sama persis dengan password baru.</li>
                            </ul>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="mt-8 flex flex-col sm:flex-row items-center gap-3">
                            <button
                                type="submit"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white font-semibold text-sm transition shadow-sm hover:shadow"
                            >
                                <i class="fas fa-floppy-disk text-sm"></i>
                                <span>Simpan Password Baru</span>
                            </button>

                            <button
                                type="reset"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-sm transition"
                            >
                                <i class="fas fa-arrow-rotate-left text-sm"></i>
                                <span>Reset Form</span>
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (!input || !icon) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
    @endpush

</x-layouts.app>
