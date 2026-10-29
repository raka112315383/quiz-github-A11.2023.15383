<x-layouts.app title="Verifikasi Pembayaran">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Verifikasi Pembayaran Pasien
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola dan verifikasi bukti transfer pembayaran hasil pemeriksaan poli dari pasien.
            </p>
        </div>

        @if($countMenunggu > 0)
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 shadow-sm animate-pulse">
            <i class="fas fa-bell text-amber-600"></i>
            {{ $countMenunggu }} Pembayaran Menunggu Verifikasi
        </div>
        @endif
    </div>

    {{-- Alerts --}}
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

    {{-- Filter Tabs --}}
    <div class="flex flex-wrap items-center gap-2 mb-6">
        <a href="{{ route('admin.pembayaran.index', ['status' => 'menunggu_verifikasi']) }}"
            class="px-4 py-2.5 rounded-xl text-xs font-semibold transition flex items-center gap-2 {{ $status === 'menunggu_verifikasi' ? 'bg-primary text-white shadow-sm' : 'bg-base-100 hover:bg-slate-100 text-slate-600 border border-slate-200' }}">
            <i class="fas fa-clock text-xs"></i>
            <span>Menunggu Verifikasi</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $status === 'menunggu_verifikasi' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }}">
                {{ $countMenunggu }}
            </span>
        </a>

        <a href="{{ route('admin.pembayaran.index', ['status' => 'lunas']) }}"
            class="px-4 py-2.5 rounded-xl text-xs font-semibold transition flex items-center gap-2 {{ $status === 'lunas' ? 'bg-primary text-white shadow-sm' : 'bg-base-100 hover:bg-slate-100 text-slate-600 border border-slate-200' }}">
            <i class="fas fa-check-circle text-xs"></i>
            <span>Sudah Lunas</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $status === 'lunas' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                {{ $countLunas }}
            </span>
        </a>

        <a href="{{ route('admin.pembayaran.index', ['status' => 'belum_bayar']) }}"
            class="px-4 py-2.5 rounded-xl text-xs font-semibold transition flex items-center gap-2 {{ $status === 'belum_bayar' ? 'bg-primary text-white shadow-sm' : 'bg-base-100 hover:bg-slate-100 text-slate-600 border border-slate-200' }}">
            <i class="fas fa-hourglass-start text-xs"></i>
            <span>Belum Bayar</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $status === 'belum_bayar' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                {{ $countBelumBayar }}
            </span>
        </a>

        <a href="{{ route('admin.pembayaran.index', ['status' => 'semua']) }}"
            class="px-4 py-2.5 rounded-xl text-xs font-semibold transition flex items-center gap-2 {{ $status === 'semua' ? 'bg-primary text-white shadow-sm' : 'bg-base-100 hover:bg-slate-100 text-slate-600 border border-slate-200' }}">
            <i class="fas fa-list text-xs"></i>
            <span>Semua Data</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $status === 'semua' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                {{ $countSemua }}
            </span>
        </a>
    </div>

    {{-- Card Table --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/60 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="fas fa-receipt text-indigo-600"></i>
                Daftar Tagihan ({{ ucfirst(str_replace('_', ' ', $status)) }})
            </h3>
            <span class="text-xs text-slate-500 font-medium">
                Ditemukan: {{ $tagihans->count() }} data
            </span>
        </div>

        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-center">ID</th>
                            <th class="px-6 py-4">Pasien</th>
                            <th class="px-6 py-4">Poli & Dokter</th>
                            <th class="px-6 py-4">Waktu Periksa / Bayar</th>
                            <th class="px-6 py-4">Total Tagihan</th>
                            <th class="px-6 py-4 text-center">Bukti Transfer</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Aksi Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-700 divide-y divide-slate-100">
                        @forelse($tagihans as $tagihan)
                        @php
                            $pasienNama = $tagihan->daftarPoli->pasien->nama ?? '-';
                            $pasienRm = $tagihan->daftarPoli->pasien->no_rm ?? '-';
                            $poliNama = $tagihan->daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? '-';
                            $dokterNama = $tagihan->daftarPoli->jadwalPeriksa->dokter->nama ?? '-';
                            $statusItem = $tagihan->status_pembayaran ?? 'belum_bayar';
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">

                            {{-- ID --}}
                            <td class="px-6 py-4 text-center font-mono text-xs text-slate-500">
                                #{{ $tagihan->id }}
                            </td>

                            {{-- Pasien --}}
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">{{ $pasienNama }}</div>
                                <div class="text-xs text-slate-500 font-mono">RM: {{ $pasienRm }}</div>
                            </td>

                            {{-- Poli & Dokter --}}
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800">{{ $poliNama }}</div>
                                <div class="text-xs text-slate-500">{{ $dokterNama }}</div>
                            </td>

                            {{-- Waktu Periksa & Bayar --}}
                            <td class="px-6 py-4 text-xs text-slate-600">
                                <div>Periksa: {{ \Carbon\Carbon::parse($tagihan->tgl_periksa)->format('d/m/Y H:i') }}</div>
                                @if($tagihan->tgl_bayar)
                                <div class="text-indigo-600 font-medium mt-0.5">
                                    Bayar: {{ \Carbon\Carbon::parse($tagihan->tgl_bayar)->format('d/m/Y H:i') }}
                                </div>
                                @endif
                            </td>

                            {{-- Total Tagihan --}}
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-slate-900">
                                    Rp {{ number_format($tagihan->biaya_periksa, 0, ',', '.') }}
                                </span>
                            </td>

                            {{-- Bukti Transfer --}}
                            <td class="px-6 py-4 text-center">
                                @if($tagihan->bukti_pembayaran)
                                <div class="flex flex-col items-center gap-1">
                                    <button type="button"
                                        onclick="openAdminProofModal('{{ asset('storage/' . $tagihan->bukti_pembayaran) }}', '{{ $pasienNama }}', '{{ number_format($tagihan->biaya_periksa, 0, ',', '.') }}', '{{ $tagihan->catatan_pembayaran }}')"
                                        class="relative group rounded-lg overflow-hidden border border-slate-200 shadow-sm w-16 h-12 bg-slate-100 flex items-center justify-center cursor-pointer">
                                        <img src="{{ asset('storage/' . $tagihan->bukti_pembayaran) }}" alt="Bukti" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs">
                                            <i class="fas fa-magnifying-glass-plus"></i>
                                        </div>
                                    </button>
                                    <span class="text-[10px] text-slate-400">Klik perbesar</span>
                                </div>
                                @else
                                <span class="text-xs text-slate-400 italic">Belum ada</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 text-center">
                                @if($statusItem === 'lunas')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fas fa-circle-check text-emerald-600"></i> Lunas
                                </span>
                                @elseif($statusItem === 'menunggu_verifikasi')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-100 text-sky-800 border border-sky-200">
                                    <i class="fas fa-clock text-sky-600"></i> Menunggu
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                    <i class="fas fa-circle-exclamation text-amber-600"></i> Belum Bayar
                                </span>
                                @endif
                            </td>

                            {{-- Aksi Verifikasi --}}
                            <td class="px-6 py-4 text-center">
                                @if($statusItem === 'menunggu_verifikasi')
                                <form action="{{ route('admin.pembayaran.konfirmasi', $tagihan->id) }}" method="POST"
                                      onsubmit="return confirm('Konfirmasi bahwa pembayaran pasien {{ $pasienNama }} sebesar Rp {{ number_format($tagihan->biaya_periksa, 0, ',', '.') }} sudah valid dan lunas?')">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition shadow-sm hover:shadow">
                                        <i class="fas fa-check-double text-xs"></i>
                                        <span>Konfirmasi Lunas</span>
                                    </button>
                                </form>
                                @elseif($statusItem === 'lunas')
                                <span class="text-xs text-emerald-600 font-medium flex items-center justify-center gap-1">
                                    <i class="fas fa-check"></i>
                                    Telah Dikonfirmasi
                                </span>
                                @else
                                <span class="text-xs text-slate-400 italic">
                                    Menunggu Bukti
                                </span>
                                @endif
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-14 text-center text-slate-400">
                                <i class="fas fa-check-double text-3xl mb-2 block text-slate-300"></i>
                                <p class="text-sm font-semibold text-slate-600">Tidak ada data tagihan</p>
                                <p class="text-xs text-slate-400 mt-0.5">Tidak ditemukan tagihan dengan status "{{ str_replace('_', ' ', $status) }}".</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ================= MODAL ADMIN LIHAT BUKTI LENGKAP ================= --}}
    <dialog id="adminProofModal" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box rounded-2xl p-6 max-w-2xl bg-base-100 border border-slate-200 shadow-2xl">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-base text-slate-800" id="proofModalPasien">Bukti Pembayaran Pasien</h3>
                    <p class="text-xs text-slate-500" id="proofModalNominal">Nominal: Rp 0</p>
                </div>
                <button type="button" onclick="closeAdminProofModal()" class="btn btn-sm btn-circle btn-ghost text-slate-400">
                    ✕
                </button>
            </div>

            {{-- Image Box --}}
            <div class="w-full max-h-[60vh] overflow-auto flex items-center justify-center bg-slate-900/5 rounded-xl p-3 border border-slate-200">
                <img id="proofModalImg" src="" alt="Bukti Pembayaran" class="max-w-full max-h-[55vh] rounded-lg object-contain shadow-sm">
            </div>

            {{-- Catatan Pasien --}}
            <div id="proofModalNotesBox" class="mt-3 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600">
                <span class="font-semibold text-slate-700 block mb-0.5">Catatan Pasien:</span>
                <span id="proofModalNotes">-</span>
            </div>

            {{-- Modal Footer --}}
            <div class="mt-5 flex items-center justify-end gap-2.5">
                <a id="proofModalDownload" href="" target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                    <i class="fas fa-arrow-up-right-from-square"></i>
                    Buka Ukuran Penuh
                </a>
                <button type="button" onclick="closeAdminProofModal()" class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs transition">
                    Tutup
                </button>
            </div>
        </div>
    </dialog>

    @push('scripts')
    <script>
        function openAdminProofModal(imgUrl, pasienNama, nominal, notes) {
            document.getElementById('proofModalImg').src = imgUrl;
            document.getElementById('proofModalDownload').href = imgUrl;
            document.getElementById('proofModalPasien').innerText = 'Bukti Pembayaran: ' + pasienNama;
            document.getElementById('proofModalNominal').innerText = 'Total Tagihan: Rp ' + nominal;

            const notesEl = document.getElementById('proofModalNotes');
            if (notes && notes.trim() !== '') {
                notesEl.innerText = notes;
                document.getElementById('proofModalNotesBox').classList.remove('hidden');
            } else {
                notesEl.innerText = 'Tidak ada catatan tambahan.';
            }

            document.getElementById('adminProofModal').showModal();
        }

        function closeAdminProofModal() {
            document.getElementById('adminProofModal').close();
        }
    </script>
    @endpush

</x-layouts.app>
