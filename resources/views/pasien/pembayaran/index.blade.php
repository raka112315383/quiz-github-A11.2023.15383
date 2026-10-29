<x-layouts.app title="Pembayaran & Tagihan">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Pembayaran & Tagihan
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Daftar tagihan dari hasil pemeriksaan poli Anda dan formulir upload bukti pembayaran.
            </p>
        </div>
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

    @if($errors->any())
    <div class="alert alert-error bg-rose-50 text-rose-800 border border-rose-200 mb-6 rounded-2xl shadow-sm p-4">
        <div class="flex items-center gap-2 mb-1 font-semibold text-sm">
            <i class="fas fa-circle-exclamation text-rose-500"></i>
            <span>Gagal mengupload bukti pembayaran:</span>
        </div>
        <ul class="list-disc list-inside text-xs space-y-1">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Info Rekening Pembayaran Klinik --}}
    <div class="card bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 text-white shadow-md rounded-2xl mb-6 overflow-hidden">
        <div class="card-body p-6 sm:p-7">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-indigo-200 border border-white/20 mb-2">
                        <i class="fas fa-building-columns"></i>
                        Informasi Rekening Pembayaran
                    </span>
                    <h2 class="text-xl font-bold">Transfer Pembayaran Klinik Poliklinik</h2>
                    <p class="text-xs text-indigo-200 mt-1 max-w-xl leading-relaxed">
                        Silakan transfer sesuai nominal total tagihan Anda ke rekening klinik berikut, kemudian upload foto bukti transfer untuk diverifikasi oleh admin.
                    </p>
                </div>

                <div class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-xl flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-white text-indigo-900 font-bold flex items-center justify-center text-sm shadow">
                        BCA
                    </div>
                    <div>
                        <div class="text-xs text-indigo-200">Bank Central Asia (BCA)</div>
                        <div class="text-lg font-mono font-bold tracking-wider">827-019-4451</div>
                        <div class="text-xs text-indigo-200">a.n. Poliklinik Sehat Bersama</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Tagihan Pemeriksaan --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/60 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="fas fa-file-invoice-dollar text-indigo-600"></i>
                Daftar Tagihan Pemeriksaan
            </h3>
            <span class="text-xs text-slate-500 font-medium">
                Total: {{ $tagihans->count() }} Tagihan
            </span>
        </div>

        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-center">No</th>
                            <th class="px-6 py-4">Poli & Dokter</th>
                            <th class="px-6 py-4">Tanggal Periksa</th>
                            <th class="px-6 py-4">Total Tagihan</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Bukti Transfer</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-700 divide-y divide-slate-100">
                        @forelse($tagihans as $index => $tagihan)
                        @php
                            $poliNama = $tagihan->daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? '-';
                            $dokterNama = $tagihan->daftarPoli->jadwalPeriksa->dokter->nama ?? '-';
                            $status = $tagihan->status_pembayaran ?? 'belum_bayar';
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">

                            {{-- No --}}
                            <td class="px-6 py-4 text-center font-mono text-xs text-slate-400">
                                {{ $index + 1 }}
                            </td>

                            {{-- Poli & Dokter --}}
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800 flex items-center gap-1.5">
                                    <i class="fas fa-hospital text-indigo-500 text-xs"></i>
                                    {{ $poliNama }}
                                </div>
                                <div class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                                    <i class="fas fa-user-doctor text-slate-400 text-xs"></i>
                                    {{ $dokterNama }}
                                </div>
                            </td>

                            {{-- Tanggal Periksa --}}
                            <td class="px-6 py-4 text-slate-600">
                                <span class="font-medium text-slate-800">
                                    {{ \Carbon\Carbon::parse($tagihan->tgl_periksa)->translatedFormat('d M Y') }}
                                </span>
                                <span class="text-xs text-slate-400 block">
                                    {{ \Carbon\Carbon::parse($tagihan->tgl_periksa)->format('H:i') }} WIB
                                </span>
                            </td>

                            {{-- Total Tagihan --}}
                            <td class="px-6 py-4">
                                <span class="text-base font-bold text-indigo-700">
                                    Rp {{ number_format($tagihan->biaya_periksa, 0, ',', '.') }}
                                </span>
                                <span class="text-xs text-slate-400 block">
                                    (Konsultasi + {{ $tagihan->detailPeriksas->count() }} Obat)
                                </span>
                            </td>

                            {{-- Status Pembayaran --}}
                            <td class="px-6 py-4 text-center">
                                @if($status === 'lunas')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-sm">
                                    <i class="fas fa-circle-check text-emerald-500"></i>
                                    Lunas
                                </span>
                                @elseif($status === 'menunggu_verifikasi')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-sky-100 text-sky-800 border border-sky-200 shadow-sm">
                                    <i class="fas fa-hourglass-half text-sky-500"></i>
                                    Menunggu Verifikasi
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200 shadow-sm">
                                    <i class="fas fa-circle-exclamation text-amber-500"></i>
                                    Belum Bayar
                                </span>
                                @endif
                            </td>

                            {{-- Bukti Pembayaran --}}
                            <td class="px-6 py-4 text-center">
                                @if($tagihan->bukti_pembayaran)
                                <button type="button"
                                    onclick="previewImageModal('{{ asset('storage/' . $tagihan->bukti_pembayaran) }}', 'Bukti Transfer Tagihan #{{ $tagihan->id }}')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition group">
                                    <i class="fas fa-image text-indigo-500 group-hover:scale-110 transition"></i>
                                    <span>Lihat Foto</span>
                                </button>
                                @else
                                <span class="text-xs text-slate-400 italic">Belum diupload</span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4 text-center">
                                @if($status === 'lunas')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-medium border border-emerald-200">
                                    <i class="fas fa-badge-check"></i>
                                    Terverifikasi
                                </span>
                                @else
                                <button type="button"
                                    onclick="openUploadModal('{{ $tagihan->id }}', '{{ number_format($tagihan->biaya_periksa, 0, ',', '.') }}', '{{ $poliNama }}', '{{ $dokterNama }}')"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl {{ $status === 'menunggu_verifikasi' ? 'bg-slate-700 hover:bg-slate-800' : 'bg-primary hover:bg-primary/90' }} text-white text-xs font-semibold transition shadow-sm hover:shadow">
                                    <i class="fas fa-cloud-arrow-up text-xs"></i>
                                    <span>{{ $status === 'menunggu_verifikasi' ? 'Upload Ulang' : 'Upload Bukti' }}</span>
                                </button>
                                @endif
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-slate-400">
                                <div class="max-w-sm mx-auto flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mb-4">
                                        <i class="fas fa-receipt"></i>
                                    </div>
                                    <h3 class="font-bold text-slate-700 text-base mb-1">Belum Ada Tagihan Pemeriksaan</h3>
                                    <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                                        Tagihan pembayaran akan muncul di sini setelah dokter menyelesaikan pemeriksaan poli Anda.
                                    </p>
                                    <a href="{{ route('pasien.riwayat.index') }}"
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                                        <i class="fas fa-clock-rotate-left"></i>
                                        Cek Riwayat Pendaftaran
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

    {{-- ================= MODAL UPLOAD BUKTI PEMBAYARAN ================= --}}
    <dialog id="uploadModal" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box rounded-2xl p-6 sm:p-7 max-w-lg bg-base-100 border border-slate-200 shadow-xl">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                        <i class="fas fa-file-arrow-up"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-slate-800">Upload Bukti Pembayaran</h3>
                        <p class="text-xs text-slate-400">Unggah foto struk transfer pembayaran Anda</p>
                    </div>
                </div>
                <button type="button" onclick="closeUploadModal()" class="btn btn-sm btn-circle btn-ghost text-slate-400">
                    ✕
                </button>
            </div>

            <form id="uploadForm" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Detail Tagihan Singkat --}}
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 mb-5 text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Poli & Dokter:</span>
                        <span class="font-semibold text-slate-800" id="modalPoliDokter">-</span>
                    </div>
                    <div class="flex justify-between items-center pt-1 border-t border-slate-200">
                        <span class="text-slate-600 font-semibold">Total yang Harus Dibayar:</span>
                        <span class="text-sm font-extrabold text-indigo-600" id="modalNominal">Rp 0</span>
                    </div>
                </div>

                {{-- Input File Bukti --}}
                <div class="form-control mb-4">
                    <label class="label pb-1.5">
                        <span class="label-text font-semibold text-slate-700 text-xs">
                            Pilih Foto / Gambar Bukti Transfer <span class="text-red-500">*</span>
                        </span>
                    </label>
                    <input type="file"
                        name="bukti_pembayaran"
                        id="bukti_input"
                        accept="image/jpeg,image/png,image/jpg"
                        onchange="handleImagePreview(event)"
                        class="file-input file-input-bordered file-input-primary w-full rounded-xl text-xs"
                        required>
                    <span class="text-[11px] text-slate-400 mt-1">Format: JPG, JPEG, PNG (Maksimal 2 MB).</span>
                </div>

                {{-- Image Preview Area --}}
                <div id="previewContainer" class="hidden mb-4">
                    <span class="text-xs font-semibold text-slate-600 block mb-1">Preview Foto:</span>
                    <div class="relative w-full h-48 bg-slate-100 rounded-xl overflow-hidden border border-slate-200 flex items-center justify-center">
                        <img id="imagePreview" src="" alt="Preview Bukti" class="w-full h-full object-contain">
                    </div>
                </div>

                {{-- Catatan Opsional --}}
                <div class="form-control mb-6">
                    <label class="label pb-1">
                        <span class="label-text font-semibold text-slate-700 text-xs">
                            Catatan Tambahan (Opsional)
                        </span>
                    </label>
                    <input type="text"
                        name="catatan_pembayaran"
                        placeholder="Contoh: Transfer via m-BCA an Ahmad"
                        class="input input-bordered w-full rounded-xl text-xs">
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex items-center justify-end gap-2.5">
                    <button type="button" onclick="closeUploadModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white font-semibold text-xs transition shadow-sm">
                        <i class="fas fa-cloud-arrow-up"></i>
                        <span>Upload & Kirim Bukti</span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- ================= MODAL LIHAT GAMBAR ================= --}}
    <dialog id="previewModal" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box rounded-2xl p-6 max-w-xl bg-base-100 border border-slate-200 shadow-xl">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="font-bold text-sm text-slate-800" id="previewModalTitle">Bukti Transfer</h3>
                <button type="button" onclick="closePreviewModal()" class="btn btn-sm btn-circle btn-ghost text-slate-400">
                    ✕
                </button>
            </div>
            <div class="w-full max-h-[70vh] overflow-auto flex items-center justify-center bg-slate-50 rounded-xl p-2 border border-slate-100">
                <img id="modalFullImage" src="" alt="Bukti Pembayaran" class="max-w-full max-h-[65vh] rounded-lg object-contain">
            </div>
            <div class="mt-4 flex justify-end">
                <button type="button" onclick="closePreviewModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                    Tutup
                </button>
            </div>
        </div>
    </dialog>

    @push('scripts')
    <script>
        function openUploadModal(periksaId, nominal, poli, dokter) {
            const form = document.getElementById('uploadForm');
            form.action = `/pasien/pembayaran/${periksaId}/upload`;
            document.getElementById('modalNominal').innerText = 'Rp ' + nominal;
            document.getElementById('modalPoliDokter').innerText = poli + ' - ' + dokter;
            document.getElementById('previewContainer').classList.add('hidden');
            document.getElementById('bukti_input').value = '';

            document.getElementById('uploadModal').showModal();
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').close();
        }

        function handleImagePreview(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = e.target.result;
                    document.getElementById('previewContainer').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }

        function previewImageModal(imageSrc, title) {
            document.getElementById('modalFullImage').src = imageSrc;
            document.getElementById('previewModalTitle').innerText = title || 'Bukti Transfer';
            document.getElementById('previewModal').showModal();
        }

        function closePreviewModal() {
            document.getElementById('previewModal').close();
        }
    </script>
    @endpush

</x-layouts.app>
