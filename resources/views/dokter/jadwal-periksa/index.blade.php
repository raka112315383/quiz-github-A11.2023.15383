<x-layouts.app title="Jadwal Periksa">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Jadwal Periksa
            </h2>
            <p class="text-sm text-slate-500 mt-1">Kelola jadwal praktek pemeriksaan pasien Anda</p>
        </div>

        <a href="{{ route('jadwal-periksa.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 
                  bg-primary hover:bg-primary/90 
                  text-white rounded-xl text-sm font-semibold transition shadow-sm">
            <i class="fas fa-plus text-xs"></i>
            Tambah Jadwal
        </a>
    </div>

    {{-- Card Table --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body p-0">

            <div class="overflow-x-auto">
                <table class="table w-full">

                    {{-- Table Head --}}
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">No.</th>
                            <th class="px-6 py-4">Hari</th>
                            <th class="px-6 py-4">Jam Mulai</th>
                            <th class="px-6 py-4">Jam Selesai</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>

                    {{-- Table Body --}}
                    <tbody class="text-sm text-slate-700 divide-y divide-slate-100">

                        @forelse($jadwals as $index => $jadwal)
                        <tr class="hover:bg-slate-50 transition">

                            <td class="px-6 py-4 text-slate-500 font-medium">
                                {{ $index + 1 }}
                            </td>

                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    {{ $jadwal->hari }}
                                </span>
                            </td>

                            <td class="px-6 py-4 font-mono font-medium text-slate-800">
                                <i class="far fa-clock text-slate-400 mr-1.5 text-xs"></i>
                                {{ date('H:i', strtotime($jadwal->jam_mulai)) }} WIB
                            </td>

                            <td class="px-6 py-4 font-mono font-medium text-slate-800">
                                <i class="far fa-clock text-slate-400 mr-1.5 text-xs"></i>
                                {{ date('H:i', strtotime($jadwal->jam_selesai)) }} WIB
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Aktif
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">

                                    {{-- Edit --}}
                                    <a href="{{ route('jadwal-periksa.edit', $jadwal->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 
                                        bg-amber-500 hover:bg-amber-600 
                                        text-white rounded-lg text-xs font-semibold transition">
                                        <i class="fas fa-pen-to-square text-xs"></i>
                                        Edit
                                    </a>

                                    {{-- Delete --}}
                                    <form action="{{ route('jadwal-periksa.destroy', $jadwal->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                            onclick="return confirm('Yakin ingin menghapus jadwal periksa ini?')" class="inline-flex items-center gap-1.5 px-3.5 py-2 
                                             bg-red-500 hover:bg-red-600 
                                             text-white rounded-lg text-xs font-semibold transition">
                                            <i class="fas fa-trash text-xs"></i>
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-16 text-slate-400">
                                <i class="fas fa-calendar-xmark text-4xl mb-3 block text-slate-300"></i>
                                Belum ada jadwal periksa yang dibuat
                            </td>
                        </tr>
                        @endforelse

                    </tbody>

                </table>
            </div>

        </div>
    </div>

</x-layouts.app>
