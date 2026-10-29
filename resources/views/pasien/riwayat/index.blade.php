<x-layouts.app title="Riwayat Pendaftaran Poli">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Riwayat Pendaftaran Poli
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Daftar seluruh riwayat pendaftaran pemeriksaan poli Anda diurutkan dari yang terbaru.
            </p>
        </div>

        <a href="{{ route('pasien.daftar') }}"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white font-semibold text-sm transition shadow-sm">
            <i class="fas fa-plus text-xs"></i>
            Daftar Poli Baru
        </a>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="alert alert-success bg-emerald-50 text-emerald-800 border border-emerald-200 mb-6 rounded-2xl shadow-sm flex items-center gap-3 p-4">
        <i class="fas fa-circle-check text-emerald-500 text-lg"></i>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-error bg-rose-50 text-rose-800 border border-rose-200 mb-6 rounded-2xl shadow-sm flex items-center gap-3 p-4">
        <i class="fas fa-circle-xmark text-rose-500 text-lg"></i>
        <span class="text-sm font-medium">{{ session('error') }}</span>
    </div>
    @endif

    {{-- Card Table --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200 overflow-hidden">
        <div class="card-body p-0">

            <div class="overflow-x-auto">
                <table class="table w-full">

                    {{-- Table Head --}}
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-center">No</th>
                            <th class="px-6 py-4">Poli</th>
                            <th class="px-6 py-4">Dokter</th>
                            <th class="px-6 py-4">Jadwal Pemeriksaan</th>
                            <th class="px-6 py-4 text-center">No. Antrian</th>
                            <th class="px-6 py-4 text-center">Status Pemeriksaan</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>

                    {{-- Table Body --}}
                    <tbody class="text-sm text-slate-700 divide-y divide-slate-100">
                        @forelse($riwayatPendaftaran as $index => $riwayat)
                        @php
                            $sudahDiperiksa = !is_null($riwayat->periksa);
                            $poliNama = $riwayat->jadwalPeriksa->dokter->poli->nama_poli ?? '-';
                            $dokterNama = $riwayat->jadwalPeriksa->dokter->nama ?? '-';
                            $jadwalHari = $riwayat->jadwalPeriksa->hari ?? '-';
                            $jamMulai = $riwayat->jadwalPeriksa->jam_mulai ? substr($riwayat->jadwalPeriksa->jam_mulai, 0, 5) : '';
                            $jamSelesai = $riwayat->jadwalPeriksa->jam_selesai ? substr($riwayat->jadwalPeriksa->jam_selesai, 0, 5) : '';
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">

                            {{-- No --}}
                            <td class="px-6 py-4 text-center font-mono text-xs text-slate-400">
                                {{ $index + 1 }}
                            </td>

                            {{-- Nama Poli --}}
                            <td class="px-6 py-4 font-semibold text-slate-800">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                                        <i class="fas fa-hospital"></i>
                                    </span>
                                    <span>{{ $poliNama }}</span>
                                </div>
                            </td>

                            {{-- Nama Dokter --}}
                            <td class="px-6 py-4 text-slate-700">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user-doctor text-slate-400 text-xs"></i>
                                    <span class="font-medium">{{ $dokterNama }}</span>
                                </div>
                            </td>

                            {{-- Tanggal Jadwal --}}
                            <td class="px-6 py-4 text-slate-600">
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-medium text-slate-800 flex items-center gap-1.5">
                                        <i class="fas fa-calendar-day text-slate-400 text-xs"></i>
                                        {{ $jadwalHari }}
                                        @if($jamMulai && $jamSelesai)
                                            ({{ $jamMulai }} - {{ $jamSelesai }})
                                        @endif
                                    </span>
                                    <span class="text-xs text-slate-400">
                                        Daftar: {{ $riwayat->created_at ? $riwayat->created_at->format('d/m/Y H:i') : '-' }}
                                    </span>
                                </div>
                            </td>

                            {{-- Nomor Antrian --}}
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 font-bold font-mono text-sm border border-indigo-100 shadow-sm">
                                    {{ $riwayat->no_antrian }}
                                </span>
                            </td>

                            {{-- Status Pemeriksaan --}}
                            <td class="px-6 py-4 text-center">
                                @if($sudahDiperiksa)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-sm">
                                    <i class="fas fa-circle-check text-emerald-500"></i>
                                    Sudah Diperiksa
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200 shadow-sm">
                                    <i class="fas fa-hourglass-half text-amber-500"></i>
                                    Menunggu Pemeriksaan
                                </span>
                                @endif
                            </td>

                            {{-- Tombol Detail --}}
                            <td class="px-6 py-4 text-center">
                                @if($sudahDiperiksa)
                                <a href="{{ route('pasien.riwayat.detail', $riwayat->id) }}"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary hover:bg-primary/90 text-white text-xs font-semibold transition shadow-sm hover:shadow"
                                    title="Lihat rincian hasil pemeriksaan dokter">
                                    <i class="fas fa-file-waveform text-xs"></i>
                                    <span>Detail</span>
                                </a>
                                @else
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 text-slate-400 text-xs font-medium cursor-not-allowed"
                                      title="Pemeriksaan belum dilakukan oleh dokter">
                                    <i class="fas fa-clock text-xs"></i>
                                    <span>Menunggu</span>
                                </span>
                                @endif
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-slate-400">
                                <div class="max-w-sm mx-auto flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mb-4">
                                        <i class="fas fa-clipboard-list"></i>
                                    </div>
                                    <h3 class="font-bold text-slate-700 text-base mb-1">Belum Ada Riwayat Pendaftaran</h3>
                                    <p class="text-xs text-slate-500 mb-5 leading-relaxed">
                                        Anda belum pernah mendaftar pemeriksaan poli. Silakan lakukan pendaftaran poli terlebih dahulu.
                                    </p>
                                    <a href="{{ route('pasien.daftar') }}"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white text-xs font-semibold transition shadow-sm">
                                        <i class="fas fa-plus"></i>
                                        Daftar Poli Sekarang
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</x-layouts.app>
