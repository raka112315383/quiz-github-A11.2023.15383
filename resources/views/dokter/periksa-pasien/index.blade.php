<x-layouts.app title="Periksa Pasien">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Daftar Pasien Menunggu Pemeriksaan
            </h2>
            <p class="text-sm text-slate-500 mt-1">Daftar pasien yang terdaftar di poli Anda</p>
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
                            <th class="px-6 py-4">No. Antrian</th>
                            <th class="px-6 py-4">Nama Pasien</th>
                            <th class="px-6 py-4">Keluhan</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>

                    {{-- Table Body --}}
                    <tbody class="text-sm text-slate-700 divide-y divide-slate-100">

                        @forelse($daftarPasien as $daftar)
                        <tr class="hover:bg-slate-50 transition">

                            <td class="px-6 py-4 font-bold text-slate-800">
                                <span class="px-3 py-1 bg-slate-100 rounded-lg text-xs font-mono font-bold">
                                    {{ $daftar->no_antrian }}
                                </span>
                            </td>

                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $daftar->pasien->nama ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                {{ $daftar->keluhan ?? '-' }}
                            </td>

                            <td class="px-6 py-4">
                                @if($daftar->periksas && $daftar->periksas->count() > 0)
                                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-green-50 text-green-700 border border-green-200">
                                    Sudah Diperiksa
                                </span>
                                @else
                                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    Menunggu
                                </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right">
                                @if($daftar->periksas && $daftar->periksas->count() > 0)
                                <a href="{{ route('periksa-pasien.create', $daftar->id) }}"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                                    <i class="fas fa-edit text-xs"></i>
                                    Edit Periksa
                                </a>
                                @else
                                <a href="{{ route('periksa-pasien.create', $daftar->id) }}"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition shadow-sm"
                                    style="background-color: #2563eb !important; color: #ffffff !important;">
                                    <i class="fas fa-stethoscope text-xs"></i>
                                    Periksa
                                </a>
                                @endif
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-sm">
                                <i class="fas fa-inbox text-3xl mb-2 block"></i>
                                Belum ada pasien yang mendaftar untuk pemeriksaan.
                            </td>
                        </tr>
                        @endforelse

                    </tbody>

                </table>
            </div>

        </div>
    </div>

</x-layouts.app>
