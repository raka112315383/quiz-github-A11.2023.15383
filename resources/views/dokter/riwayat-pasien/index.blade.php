<x-layouts.app title="Riwayat Pasien">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Riwayat Pasien
            </h2>
            <p class="text-sm text-slate-500 mt-1">Daftar riwayat pemeriksaan pasien yang pernah Anda tangani</p>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="alert alert-success mb-4 rounded-xl shadow-sm">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Card Table --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body p-0">

            <div class="overflow-x-auto">
                <table class="table w-full">

                    {{-- Table Head --}}
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Nama Pasien</th>
                            <th class="px-6 py-4">Keluhan</th>
                            <th class="px-6 py-4">Tanggal Pemeriksaan</th>
                            <th class="px-6 py-4">Biaya Pemeriksaan</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>

                    {{-- Table Body --}}
                    <tbody class="text-sm text-slate-700 divide-y divide-slate-100">

                        @forelse($riwayatPasien as $riwayat)
                        <tr class="hover:bg-slate-50 transition">

                            <td class="px-6 py-4 font-bold text-slate-800">
                                <span class="px-3 py-1 bg-slate-100 rounded-lg text-xs font-mono font-bold">
                                    {{ $riwayat->id }}
                                </span>
                            </td>

                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $riwayat->daftarPoli->pasien->nama ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                {{ $riwayat->daftarPoli->keluhan ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                {{ \Carbon\Carbon::parse($riwayat->tgl_periksa)->format('d/m/Y H:i') }}
                            </td>

                            <td class="px-6 py-4 font-semibold text-[#2d4499]">
                                Rp {{ number_format($riwayat->biaya_periksa, 0, ',', '.') }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('riwayat-pasien.detail', $riwayat->id) }}"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition shadow-sm"
                                    style="background-color: #2563eb !important; color: #ffffff !important;">
                                    <i class="fas fa-eye text-xs"></i>
                                    Detail
                                </a>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                                <i class="fas fa-notes-medical text-3xl mb-2 block"></i>
                                Belum ada riwayat pemeriksaan pasien.
                            </td>
                        </tr>
                        @endforelse

                    </tbody>

                </table>
            </div>

        </div>
    </div>

</x-layouts.app>
