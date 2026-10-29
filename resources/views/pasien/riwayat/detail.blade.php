<x-layouts.app title="Detail Pemeriksaan Poli">

    {{-- Header & Back Button --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('pasien.riwayat.index') }}"
                class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition shadow-sm"
                title="Kembali ke Riwayat">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">
                    Detail Pemeriksaan Poli
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Rincian hasil pemeriksaan medis, catatan dokter, dan resep obat.
                </p>
            </div>
        </div>

        <div>
            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-sm">
                <i class="fas fa-circle-check text-emerald-600"></i>
                Pemeriksaan Selesai
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kolom Kiri (2 Kolom): Informasi Pasien & Catatan Dokter & Obat --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- 1. Kartu Informasi Pendaftaran & Pasien --}}
            <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                        <i class="fas fa-id-card-clip text-indigo-500"></i>
                        Informasi Pendaftaran
                    </h3>
                    <span class="text-xs font-mono font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 rounded-lg">
                        Antrian #{{ $daftarPoli->no_antrian }}
                    </span>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-xs text-slate-400 block mb-0.5">Nama Pasien</span>
                        <span class="font-semibold text-slate-800">{{ $daftarPoli->pasien->nama ?? '-' }}</span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block mb-0.5">Nomor Rekam Medis</span>
                        <span class="font-mono font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded text-xs">
                            {{ $daftarPoli->pasien->no_rm ?? '-' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block mb-0.5">Poli Tujuan</span>
                        <span class="font-semibold text-indigo-700 flex items-center gap-1.5">
                            <i class="fas fa-hospital text-xs"></i>
                            {{ $daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? '-' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block mb-0.5">Dokter Pemeriksa</span>
                        <span class="font-semibold text-slate-800 flex items-center gap-1.5">
                            <i class="fas fa-user-doctor text-slate-400 text-xs"></i>
                            {{ $daftarPoli->jadwalPeriksa->dokter->nama ?? '-' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block mb-0.5">Jadwal Periksa</span>
                        <span class="text-slate-700">
                            {{ $daftarPoli->jadwalPeriksa->hari ?? '-' }}
                            ({{ substr($daftarPoli->jadwalPeriksa->jam_mulai ?? '', 0, 5) }} - {{ substr($daftarPoli->jadwalPeriksa->jam_selesai ?? '', 0, 5) }})
                        </span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block mb-0.5">Waktu Diperiksa</span>
                        <span class="text-slate-700">
                            {{ \Carbon\Carbon::parse($periksa->tgl_periksa)->translatedFormat('d F Y, H:i') }}
                        </span>
                    </div>

                    <div class="md:col-span-2 pt-2 border-t border-slate-100">
                        <span class="text-xs text-slate-400 block mb-1">Keluhan Saat Mendaftar</span>
                        <p class="text-xs text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100 leading-relaxed">
                            {{ $daftarPoli->keluhan ?: 'Tidak ada keluhan tertulis' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- 2. Kartu Catatan Dokter --}}
            <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                    <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                        <i class="fas fa-clipboard-user text-indigo-500"></i>
                        Catatan Dokter
                    </h3>
                </div>
                <div class="p-6">
                    @if($periksa->catatan)
                    <div class="p-4 rounded-xl bg-indigo-50/40 border border-indigo-100 text-slate-700 text-sm leading-relaxed whitespace-pre-line">
                        {{ $periksa->catatan }}
                    </div>
                    @else
                    <p class="text-sm text-slate-400 italic">
                        Tidak ada catatan khusus yang diberikan oleh dokter.
                    </p>
                    @endif
                </div>
            </div>

            {{-- 3. Kartu Obat yang Diresepkan --}}
            <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                        <i class="fas fa-pills text-indigo-500"></i>
                        Daftar Obat yang Diresepkan
                    </h3>
                    <span class="text-xs text-slate-500">
                        {{ $periksa->detailPeriksas->count() }} jenis obat
                    </span>
                </div>

                <div class="p-0">
                    @if($periksa->detailPeriksas && $periksa->detailPeriksas->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-3.5 text-center w-12">#</th>
                                    <th class="px-6 py-3.5">Nama Obat</th>
                                    <th class="px-6 py-3.5">Kemasan</th>
                                    <th class="px-6 py-3.5 text-right">Harga</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm text-slate-700 divide-y divide-slate-100">
                                @foreach($periksa->detailPeriksas as $index => $detail)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-6 py-3.5 text-center font-mono text-xs text-slate-400">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-6 py-3.5 font-semibold text-slate-800">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-capsules text-indigo-400 text-xs"></i>
                                            <span>{{ $detail->obat->nama_obat ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5 text-slate-500 text-xs">
                                        {{ $detail->obat->kemasan ?? '-' }}
                                    </td>
                                    <td class="px-6 py-3.5 text-right font-medium text-slate-700">
                                        Rp {{ number_format($detail->obat->harga ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="p-8 text-center text-slate-400">
                        <i class="fas fa-prescription-bottle text-2xl mb-2 block"></i>
                        <p class="text-sm">Tidak ada resep obat pada pemeriksaan ini.</p>
                    </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- Kolom Kanan (1 Kolom): Rincian Biaya & Aksi --}}
        <div class="space-y-6">

            {{-- Kartu Total Biaya --}}
            <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200 overflow-hidden">
                <div class="p-6 bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 text-white">
                    <span class="text-xs uppercase tracking-wider text-indigo-200 font-semibold block mb-1">
                        Total Biaya Pemeriksaan
                    </span>
                    <div class="text-3xl font-extrabold tracking-tight">
                        Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}
                    </div>
                    <p class="text-xs text-indigo-100 mt-2 flex items-center gap-1.5 opacity-90">
                        <i class="fas fa-circle-info"></i>
                        Termasuk jasa konsultasi dokter dan obat.
                    </p>
                </div>

                <div class="p-6 space-y-3 text-xs">
                    <div class="flex items-center justify-between text-slate-500 py-1.5 border-b border-slate-100">
                        <span>Jasa Periksa Dokter</span>
                        <span class="font-semibold text-slate-700">Rp 150.000</span>
                    </div>

                    @php
                        $totalObat = $periksa->detailPeriksas->sum(fn($d) => $d->obat->harga ?? 0);
                    @endphp
                    <div class="flex items-center justify-between text-slate-500 py-1.5 border-b border-slate-100">
                        <span>Subtotal Obat</span>
                        <span class="font-semibold text-slate-700">Rp {{ number_format($totalObat, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex items-center justify-between text-slate-800 font-bold pt-2 text-sm">
                        <span>Total Akhir</span>
                        <span class="text-indigo-600">Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="px-6 pb-6">
                    <a href="{{ route('pasien.riwayat.index') }}"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                        <i class="fas fa-arrow-left"></i>
                        Kembali ke Riwayat
                    </a>
                </div>
            </div>

            {{-- Kartu Bantuan / Catatan --}}
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-start gap-3">
                <i class="fas fa-lightbulb text-amber-500 text-base mt-0.5"></i>
                <div class="leading-relaxed">
                    <strong>Catatan Pasien:</strong> Simpan halaman ini sebagai bukti pemeriksaan dan rincian obat yang telah Anda terima.
                </div>
            </div>

        </div>

    </div>

</x-layouts.app>
