<x-layouts.app title="Dashboard Pasien">

    {{-- Main Container --}}
    <div class="space-y-6">

        {{-- Welcome & Overview Bar --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                    <span>👋</span> Halo Selamat Datang Pasien, <span class="text-blue-600">{{ $pasien->nama }}</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Pantau nomor antrian yang sedang dipanggil di setiap poli secara real-time via WebSocket.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                {{-- WebSocket Connection Badge --}}
                <div id="ws-status-badge" class="px-3.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-xs" title="Terhubung ke Laravel Reverb WebSocket Server">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span id="ws-status-text">Reverb Live</span>
                </div>

                {{-- No RM Chip --}}
                <div class="px-3.5 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 flex items-center gap-2">
                    <span class="text-slate-400">No. RM:</span>
                    <span class="font-mono text-blue-600 font-bold">{{ $pasien->no_rm ?? '-' }}</span>
                </div>

                {{-- Status Antrian Chip --}}
                <div id="badge-status-pasien" class="px-3.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1.5 {{ $antrianAktif ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                    <span class="w-2 h-2 rounded-full {{ $antrianAktif ? 'bg-blue-600 animate-pulse' : 'bg-slate-400' }}"></span>
                    <span>{{ $antrianAktif ? 'Antrian Aktif' : 'Tidak Ada Antrian' }}</span>
                </div>

                {{-- Button Daftar Poli --}}
                @if(!$antrianAktif)
                <a href="{{ route('pasien.daftar') }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm transition-all hover:shadow"
                    style="background-color: #2563eb !important; color: #ffffff !important;">
                    <i class="fas fa-plus text-xs"></i>
                    Daftar Poli
                </a>
                @endif
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success') || session('message'))
        <div class="alert alert-success rounded-xl shadow-sm text-xs sm:text-sm">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') ?? session('message') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-error rounded-xl shadow-sm text-xs sm:text-sm">
            <i class="fas fa-circle-xmark"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        {{-- ================= 1. BANNER ANTRIAN AKTIF ================= --}}
        <div id="active-queue-banner"
            class="{{ $antrianAktif ? 'block' : 'hidden' }} w-full rounded-2xl shadow-lg p-6 sm:p-7 text-white relative overflow-hidden transition-all duration-300"
            style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #3b82f6 100%); box-shadow: 0 10px 25px -5px rgba(29, 78, 216, 0.45);">

            {{-- Decorative Background Watermark --}}
            <div class="absolute -right-10 -bottom-10 w-72 h-72 rounded-full bg-white/5 pointer-events-none blur-3xl"></div>
            <div class="absolute right-1/3 -top-12 w-48 h-48 rounded-full bg-blue-400/10 pointer-events-none blur-2xl"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

                {{-- Sisi Kiri: Informasi Poli, Dokter, dan Jadwal Periksa --}}
                <div class="space-y-4">
                    {{-- Header Banner --}}
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 border border-white/20 text-[11px] font-bold uppercase tracking-wider text-blue-100">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        ANTRIAN AKTIF ANDA
                    </div>

                    <div class="space-y-3">
                        {{-- Poliklinik --}}
                        <div>
                            <span class="text-[11px] uppercase tracking-wider text-blue-200 font-semibold block">
                                Poliklinik
                            </span>
                            <div id="live-banner-poli" class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mt-0.5">
                                {{ $antrianAktif->jadwalPeriksa->dokter->poli->nama_poli ?? 'Poli' }}
                            </div>
                        </div>

                        {{-- Dokter --}}
                        <div>
                            <span class="text-[11px] uppercase tracking-wider text-blue-200 font-semibold block">
                                Dokter
                            </span>
                            <div id="live-banner-dokter" class="text-base sm:text-lg font-bold text-blue-50 mt-0.5">
                                {{ $antrianAktif->jadwalPeriksa->dokter->nama ?? 'Dokter' }}
                            </div>
                        </div>

                        {{-- Jadwal Periksa --}}
                        <div>
                            <span class="text-[11px] uppercase tracking-wider text-blue-200 font-semibold block">
                                Jadwal Periksa
                            </span>
                            <div id="live-banner-jadwal" class="text-sm font-semibold text-blue-100 mt-0.5 flex items-center gap-1.5">
                                <i class="far fa-clock text-xs text-blue-200"></i>
                                <span>{{ $antrianAktif->jadwalPeriksa->hari ?? '-' }} ({{ substr($antrianAktif->jadwalPeriksa->jam_mulai ?? '', 0, 5) }} - {{ substr($antrianAktif->jadwalPeriksa->jam_selesai ?? '', 0, 5) }})</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sisi Kanan: Kartu Nomor Anda & Kartu Sedang Dilayani --}}
                <div class="flex items-center justify-start lg:justify-end gap-3 sm:gap-4 shrink-0">

                    {{-- Card 1: Nomor Anda --}}
                    <div class="rounded-2xl p-4 sm:p-5 flex flex-col items-center justify-center min-w-[120px] sm:min-w-[140px] text-center border border-white/25 backdrop-blur-md transition hover:scale-105"
                        style="background: rgba(255, 255, 255, 0.16);">
                        <span class="text-xs font-semibold text-blue-100 mb-1.5">
                            Nomor Anda
                        </span>
                        <span id="live-banner-nomor-anda" class="text-4xl sm:text-5xl font-black text-white leading-none tracking-tight">
                            {{ $antrianAktif->no_antrian ?? '-' }}
                        </span>
                    </div>

                    {{-- Card 2: SEDANG DILAYANI / DIPANGGIL (White Card dengan Live Update) --}}
                    <div class="rounded-2xl p-4 sm:p-5 flex flex-col items-center justify-center min-w-[140px] sm:min-w-[160px] text-center bg-white shadow-2xl border border-white transition hover:scale-105">
                        <span class="text-[11px] font-extrabold tracking-wider uppercase text-slate-700 mb-1.5">
                            SEDANG DILAYANI
                        </span>
                        <span id="live-sedang-dilayani"
                            class="text-4xl sm:text-5xl font-black text-blue-600 leading-none tracking-tight transition-all duration-300">
                            {{ $nomorSedangDilayani ?? '-' }}
                        </span>

                        {{-- Live Update Indicator --}}
                        <div class="mt-2.5 flex items-center justify-center gap-1.5 text-[10px] font-bold text-slate-600 bg-slate-50 px-2.5 py-1 rounded-full border border-slate-200">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="font-medium tracking-wide">Live Update</span>
                        </div>
                    </div>

                </div>

            </div>

            {{-- Dynamic Alert jika giliran pasien sedang dipanggil --}}
            <div id="live-turn-alert"
                class="{{ ($antrianAktif && $nomorSedangDilayani !== null && $nomorSedangDilayani == $antrianAktif->no_antrian) ? 'flex' : 'hidden' }} mt-5 p-3 rounded-xl bg-white/20 border border-white/30 backdrop-blur-md items-center gap-2.5 text-xs font-bold text-white animate-bounce">
                <i class="fas fa-bullhorn text-amber-300 text-sm"></i>
                <span id="live-turn-alert-text">Sekarang adalah giliran nomor antrian Anda! Silakan bersiap dan masuk ke ruang pemeriksaan dokter.</span>
            </div>

        </div>

        {{-- Pesan Kosong / Empty State Jika Tidak Memiliki Antrian Aktif --}}
        <div id="empty-queue-banner"
            class="{{ $antrianAktif ? 'hidden' : 'block' }} w-full bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-8 transition-all duration-300">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-6 text-center sm:text-left">
                <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-5">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl shrink-0 border border-blue-100 shadow-inner">
                        <i class="fas fa-ticket-simple"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-800">
                            Anda Belum Memiliki Antrian Aktif
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-xl">
                            Saat ini Anda belum terdaftar dalam antrian periksa. Silakan melakukan pendaftaran di poliklinik tujuan untuk mendapatkan nomor antrian konsultasi dokter.
                        </p>
                    </div>
                </div>

                <a href="{{ route('pasien.daftar') }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition-all shrink-0"
                    style="background-color: #2563eb !important; color: #ffffff !important;">
                    <i class="fas fa-calendar-plus text-xs"></i>
                    Daftar Poli Sekarang
                </a>
            </div>
        </div>

        {{-- ================= 2. NOMOR ANTRIAN SEDANG DIPANGGIL DI SETIAP POLI ================= --}}
        <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-bullhorn text-emerald-600"></i>
                        Nomor Antrian Sedang Dipanggil di Setiap Poli
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Menampilkan status nomor antrian yang sedang dipanggil oleh dokter di tiap poli secara real-time via WebSocket
                    </p>
                </div>

                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold self-start sm:self-auto">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    Echo WebSocket Sync
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="poli-cards-grid">
                @forelse($polisStatus as $poli)
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/60 hover:bg-white hover:shadow-md transition-all duration-300 relative overflow-hidden group"
                    id="card-poli-{{ $poli['id'] }}">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-extrabold text-slate-800 truncate">{{ $poli['nama_poli'] }}</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" title="Live channel"></span>
                    </div>

                    <div class="mt-3 flex items-baseline justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Sedang Dipanggil</span>
                            <span data-poli-badge="{{ $poli['id'] }}"
                                class="text-3xl font-black text-blue-600 inline-block transition-all duration-300">
                                {{ $poli['sedang_dipanggil'] ? '#' . $poli['sedang_dipanggil'] : '-' }}
                            </span>
                        </div>

                        <span class="text-[11px] font-semibold text-slate-500 bg-white px-2 py-1 rounded-lg border border-slate-200 shadow-2xs">
                            <i class="fas fa-users text-slate-400 text-[10px] mr-1"></i>
                            <span data-poli-menunggu="{{ $poli['id'] }}">{{ $poli['total_menunggu'] ?? 0 }}</span> menunggu
                        </span>
                    </div>
                </div>
                @empty
                <div class="col-span-full py-6 text-center text-slate-400 text-xs">
                    Belum ada data poliklinik terdaftar.
                </div>
                @endforelse
            </div>
        </div>

        {{-- ================= 3. TABEL DAFTAR JADWAL POLIKLINIK & DOKTER ================= --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

            {{-- Header Table --}}
            <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-calendar-days text-blue-600"></i>
                        Daftar Jadwal Poliklinik & Dokter
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Tabel antrian poliklinik terupdate otomatis tanpa reload halaman
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Search Input --}}
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="schedule-search" placeholder="Cari poli / dokter..."
                            class="pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-blue-500 text-slate-700 w-44 sm:w-56 transition">
                    </div>

                    {{-- Live badge --}}
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="hidden sm:inline">Reverb WebSocket</span>
                    </div>
                </div>
            </div>

            {{-- Table Content --}}
            <div class="overflow-x-auto">
                <table class="table w-full" id="schedule-table">
                    <thead class="bg-slate-50 text-slate-600 text-[11px] uppercase tracking-wider border-b border-slate-200 font-bold">
                        <tr>
                            <th class="px-6 py-3.5 text-center w-16">No</th>
                            <th class="px-6 py-3.5 text-left">Poliklinik</th>
                            <th class="px-6 py-3.5 text-left">Dokter</th>
                            <th class="px-6 py-3.5 text-left">Hari</th>
                            <th class="px-6 py-3.5 text-left">Jam Periksa</th>
                            <th class="px-6 py-3.5 text-center">Sedang Dipanggil</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs text-slate-700 divide-y divide-slate-100">
                        @forelse($jadwals as $index => $jadwal)
                        @php
                            $sedang = $sedangDilayaniPerJadwal[$jadwal->id] ?? null;
                            $poliNama = $jadwal->dokter->poli->nama_poli ?? '-';
                            $dokterNama = $jadwal->dokter->nama ?? '-';
                        @endphp
                        <tr class="schedule-row hover:bg-slate-50/80 transition"
                            data-search="{{ strtolower($poliNama . ' ' . $dokterNama . ' ' . $jadwal->hari) }}">

                            {{-- No --}}
                            <td class="px-6 py-4 text-center font-bold text-slate-500">
                                {{ $loop->iteration }}
                            </td>

                            {{-- Poli --}}
                            <td class="px-6 py-4 font-semibold text-slate-800">
                                <div class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold border border-blue-100">
                                        <i class="fas fa-stethoscope"></i>
                                    </span>
                                    <span>{{ $poliNama }}</span>
                                </div>
                            </td>

                            {{-- Dokter --}}
                            <td class="px-6 py-4 font-medium text-slate-800">
                                {{ $dokterNama }}
                            </td>

                            {{-- Hari --}}
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $jadwal->hari }}
                                </span>
                            </td>

                            {{-- Jam Periksa --}}
                            <td class="px-6 py-4 font-medium text-slate-600">
                                <div class="flex items-center gap-1.5">
                                    <i class="far fa-clock text-slate-400 text-xs"></i>
                                    <span>{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}</span>
                                </div>
                            </td>

                            {{-- Sedang Dipanggil (Live WebSocket Badge) --}}
                            <td class="px-6 py-4 text-center">
                                <span data-jadwal-badge="{{ $jadwal->id }}"
                                    class="inline-flex items-center justify-center min-w-[50px] px-3 py-1 rounded-full text-xs font-bold transition-all duration-300 {{ $sedang ? 'bg-blue-100 text-blue-700 border border-blue-200 shadow-xs' : 'bg-slate-100 text-slate-400 border border-slate-200' }}">
                                    {{ $sedang ? '#' . $sedang : '-' }}
                                </span>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i class="fas fa-calendar-xmark text-3xl mb-2 text-slate-300 block"></i>
                                <p class="font-medium text-sm text-slate-600">Belum Ada Jadwal Praktik</p>
                                <p class="text-xs text-slate-400 mt-0.5">Jadwal dokter poliklinik belum tersedia saat ini.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Table Footer Summary --}}
            <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Total Jadwal: <strong>{{ $jadwals->count() }}</strong></span>
                <span class="flex items-center gap-1.5 text-emerald-600 font-medium">
                    <i class="fas fa-tower-broadcast text-emerald-500 animate-pulse"></i>
                    Terhubung ke WebSocket Server (Laravel Reverb & Echo)
                </span>
            </div>

        </div>

    </div>

    {{-- Script Real-Time Antrian: Laravel Echo & Reverb WebSocket --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const liveUrl = "{{ route('pasien.antrian.live') }}";
            const currentUserId = {{ Auth::id() }};
            let currentActiveNoAntrian = {{ $antrianAktif->no_antrian ?? 'null' }};
            let currentActiveJadwalId = {{ $antrianAktif->id_jadwal ?? 'null' }};
            let currentActivePoliId = {{ $antrianAktif->jadwalPeriksa->dokter->poli->id ?? 'null' }};

            // Search filter untuk jadwal
            const searchInput = document.getElementById('schedule-search');
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const query = this.value.toLowerCase().trim();
                    const rows = document.querySelectorAll('.schedule-row');
                    rows.forEach(row => {
                        const text = row.getAttribute('data-search') || '';
                        row.style.display = text.includes(query) ? '' : 'none';
                    });
                });
            }

            // =========================================================================
            // 1. UPDATE UI STATE DARI EVENT REAL-TIME (ECHO & FALLBACK)
            // =========================================================================
            function applyQueueUpdates(data) {
                if (!data) return;

                // A. Update status antrian per poli ("Menampilkan nomor antrian yang sedang dipanggil di setiap poli")
                if (Array.isArray(data.polis)) {
                    data.polis.forEach(poli => {
                        const badge = document.querySelector(`[data-poli-badge="${poli.id}"]`);
                        if (badge) {
                            const newText = poli.sedang_dipanggil !== null ? '#' + poli.sedang_dipanggil : '-';
                            if (badge.textContent.trim() !== newText) {
                                badge.textContent = newText;
                                badge.classList.add('scale-125', 'text-amber-500');
                                setTimeout(() => badge.classList.remove('scale-125', 'text-amber-500'), 400);
                            }
                        }

                        const waitingEl = document.querySelector(`[data-poli-menunggu="${poli.id}"]`);
                        if (waitingEl && typeof poli.total_menunggu !== 'undefined') {
                            waitingEl.textContent = poli.total_menunggu;
                        }
                    });
                }

                // B. Update baris jadwal di tabel
                if (Array.isArray(data.jadwals)) {
                    data.jadwals.forEach(item => {
                        const badge = document.querySelector(`[data-jadwal-badge="${item.id}"]`);
                        if (badge) {
                            const newDisplay = item.sedang_dilayani !== null ? '#' + item.sedang_dilayani : '-';
                            if (badge.textContent.trim() !== newDisplay) {
                                badge.textContent = newDisplay;
                                if (item.sedang_dilayani !== null) {
                                    badge.className = 'inline-flex items-center justify-center min-w-[50px] px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200 shadow-xs transition-all duration-300 scale-110';
                                    setTimeout(() => badge.classList.remove('scale-110'), 350);
                                } else {
                                    badge.className = 'inline-flex items-center justify-center min-w-[50px] px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-400 border border-slate-200 transition-all duration-300';
                                }
                            }
                        }
                    });
                }

                // C. Update Banner Antrian Aktif Pasien jika ada
                const activeBanner = document.getElementById('active-queue-banner');
                const emptyBanner = document.getElementById('empty-queue-banner');
                const sedangDilayaniEl = document.getElementById('live-sedang-dilayani');
                const turnAlert = document.getElementById('live-turn-alert');
                const turnAlertText = document.getElementById('live-turn-alert-text');
                const statusBadge = document.getElementById('badge-status-pasien');

                if (data.has_active && data.antrian_aktif) {
                    if (activeBanner) activeBanner.classList.remove('hidden');
                    if (emptyBanner) emptyBanner.classList.add('hidden');

                    if (sedangDilayaniEl) {
                        const newNum = String(data.antrian_aktif.sedang_dilayani ?? '-');
                        if (sedangDilayaniEl.textContent.trim() !== newNum) {
                            sedangDilayaniEl.classList.add('scale-110', 'text-amber-500');
                            setTimeout(() => {
                                sedangDilayaniEl.textContent = newNum;
                                sedangDilayaniEl.classList.remove('scale-110', 'text-amber-500');
                            }, 250);
                        }
                    }

                    if (turnAlert) {
                        if (data.antrian_aktif.is_your_turn) {
                            turnAlert.classList.remove('hidden');
                            turnAlert.classList.add('flex');
                        } else {
                            turnAlert.classList.add('hidden');
                            turnAlert.classList.remove('flex');
                        }
                    }
                } else if (typeof data.has_active !== 'undefined' && !data.has_active) {
                    // Pemeriksaan selesai atau tidak punya antrian
                    if (activeBanner && !activeBanner.classList.contains('hidden')) {
                        activeBanner.classList.add('hidden');
                    }
                    if (emptyBanner && emptyBanner.classList.contains('hidden')) {
                        emptyBanner.classList.remove('hidden');
                    }
                    if (statusBadge) {
                        statusBadge.className = 'px-3.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1.5 bg-slate-100 text-slate-600 border border-slate-200';
                        statusBadge.innerHTML = '<span class="w-2 h-2 rounded-full bg-slate-400"></span><span>Tidak Ada Antrian</span>';
                    }
                }
            }

            // =========================================================================
            // 2. KONEKSI WEBSOCKET VIA LARAVEL ECHO (LARAVEL REVERB)
            // =========================================================================
            if (window.Echo) {
                console.log('📡 [Laravel Echo] Menghubungkan ke Laravel Reverb WebSocket...');

                // Channel publik 'antrian'
                window.Echo.channel('antrian')
                    .listen('.AntrianUpdated', (event) => {
                        console.log('⚡ [WebSocket Reverb Event]:', event);
                        handleWebSocketEvent(event);
                    })
                    .listen('AntrianUpdated', (event) => {
                        console.log('⚡ [WebSocket Reverb Event]:', event);
                        handleWebSocketEvent(event);
                    });

                // Update UI indikator status Reverb
                const wsBadge = document.getElementById('ws-status-badge');
                const wsText = document.getElementById('ws-status-text');

                if (window.Echo.connector && window.Echo.connector.pusher) {
                    window.Echo.connector.pusher.connection.bind('connected', function () {
                        console.log('✅ [Laravel Echo] Terhubung sukses ke Reverb Server!');
                        if (wsBadge) {
                            wsBadge.className = 'px-3.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-xs';
                        }
                        if (wsText) wsText.textContent = 'Reverb WebSocket Live';
                    });

                    window.Echo.connector.pusher.connection.bind('disconnected', function () {
                        console.warn('⚠️ [Laravel Echo] Terputus dari Reverb Server, menggunakan polling fallback.');
                        if (wsBadge) {
                            wsBadge.className = 'px-3.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1.5 bg-amber-50 text-amber-700 border border-amber-200';
                        }
                        if (wsText) wsText.textContent = 'Auto-Sync Poll';
                    });
                }
            } else {
                console.warn('Laravel Echo belum tersedia pada window. Menggunakan polling fallback.');
            }

            // Handle event dari WebSocket
            function handleWebSocketEvent(event) {
                // Terapkan data jadwal & poli langsung dari payload broadcast
                applyQueueUpdates({
                    polis: event.polis,
                    jadwals: event.jadwals
                });

                // Cek apakah nomor antrian milik user dipanggil
                if (currentActiveNoAntrian && currentActiveJadwalId) {
                    if (event.idJadwal === currentActiveJadwalId && event.noAntrian === currentActiveNoAntrian) {
                        const turnAlert = document.getElementById('live-turn-alert');
                        const turnAlertText = document.getElementById('live-turn-alert-text');
                        if (turnAlert) {
                            turnAlert.classList.remove('hidden');
                            turnAlert.classList.add('flex');
                            if (turnAlertText) {
                                turnAlertText.textContent = `Nomor antrian Anda (#${currentActiveNoAntrian}) sedang dipanggil dokter! Silakan menuju ruang ${event.namaPoli ?? 'pemeriksaan'}.`;
                            }
                        }

                        // Notifikasi suara web speech sederhana
                        try {
                            if ('speechSynthesis' in window) {
                                const msg = new SpeechSynthesisUtterance(`Nomor antrian ${currentActiveNoAntrian}, silakan menuju ruang pemeriksaan`);
                                msg.lang = 'id-ID';
                                window.speechSynthesis.speak(msg);
                            }
                        } catch(e) {}
                    }
                }

                // Jika aksi adalah 'selesai' dan menyangkut pasien login, refresh status antrian aktif
                if (event.aksi === 'selesai' && event.idPasien === currentUserId) {
                    fetchLiveQueue();
                }
            }

            // =========================================================================
            // 3. POLLING FALLBACK (MENJAMIN KONSISTENSI DATA APABILA WEBSOCKET DISCONNECT)
            // =========================================================================
            async function fetchLiveQueue() {
                try {
                    const response = await fetch(liveUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) return;
                    const data = await response.json();
                    applyQueueUpdates(data);

                    if (data.antrian_aktif) {
                        currentActiveNoAntrian = data.antrian_aktif.no_antrian;
                        currentActiveJadwalId = data.antrian_aktif.id;
                    } else {
                        currentActiveNoAntrian = null;
                        currentActiveJadwalId = null;
                    }
                } catch (e) {
                    // Silently ignore network hiccups
                }
            }

            // Jalankan polling sebagai backup periodik setiap 6 detik
            setInterval(fetchLiveQueue, 6000);
        });
    </script>
    @endpush

</x-layouts.app>