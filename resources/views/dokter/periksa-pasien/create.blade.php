<x-layouts.app title="Periksa Pasien">

    {{-- Back & Title --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('periksa-pasien.index') }}" class="text-slate-600 hover:text-slate-900 transition flex items-center justify-center w-8 h-8 rounded-lg hover:bg-slate-100">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">
            Periksa Pasien
        </h2>
    </div>

    {{-- Card Form --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body p-8">

            <form action="{{ route('periksa-pasien.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id_daftar_poli" value="{{ $id ?? 1 }}">
                <input type="hidden" name="obat_json" id="obat_json" value="[]">
                <input type="hidden" name="biaya_periksa" id="biaya_periksa" value="0">

                {{-- Pilih Obat --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Pilih Obat <span class="text-red-500">*</span>
                    </label>
                    <select id="select_obat" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none cursor-pointer">
                        <option value="" disabled selected>-- Pilih Obat --</option>
                        @foreach($obats as $obat)
                        <option value="{{ $obat->id }}" data-nama="{{ $obat->nama_obat }}" data-harga="{{ $obat->harga }}">
                            {{ $obat->nama_obat }} - {{ $obat->kemasan }} (Rp {{ number_format($obat->harga, 0, ',', '.') }})
                        </option>
                        @endforeach
                    </select>
                    @error('obat_json')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Obat Terpilih --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Obat Terpilih
                    </label>
                    <div id="obat_terpilih_container" class="flex flex-wrap gap-2 min-h-[42px] items-center p-2.5 rounded-lg border border-dashed border-slate-200 bg-slate-50/50">
                        <span id="empty_notice" class="text-xs text-slate-400 italic">Belum ada obat yang dipilih</span>
                    </div>
                </div>

                {{-- Total Harga --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Total Harga
                    </label>
                    <div class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-slate-800 text-sm font-semibold" id="total_harga_display">
                        Rp 0
                    </div>
                </div>

                {{-- Catatan (Opsional) --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Catatan (Opsional)
                    </label>
                    <textarea name="catatan" rows="4" placeholder="Masukkan catatan..."
                        class="w-full bg-white border border-slate-300 rounded-lg p-3 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none">{{ old('catatan') }}</textarea>
                    @error('catatan')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Buttons --}}
                <div class="flex items-center gap-3">
                    <button type="submit"
                        style="background-color: #1e2d6b !important; color: #ffffff !important;"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-[#1e2d6b] text-white font-semibold text-sm transition shadow-sm hover:opacity-90">
                        <i class="fas fa-floppy-disk text-xs"></i>
                        Simpan
                    </button>
                    <a href="{{ route('periksa-pasien.index') }}"
                        style="background-color: #f1f5f9; color: #475569;"
                        class="px-6 py-2.5 rounded-lg bg-slate-100 text-slate-600 font-semibold text-sm transition hover:bg-slate-200">
                        Batal
                    </a>
                </div>

            </form>

        </div>
    </div>

    @push('scripts')
    <script>
        let selectedObats = [];

        document.getElementById('select_obat').addEventListener('change', function () {
            const option = this.options[this.selectedIndex];
            if (!option.value) return;

            const id = parseInt(option.value);
            const nama = option.getAttribute('data-nama');
            const harga = parseInt(option.getAttribute('data-harga')) || 0;

            if (selectedObats.some(o => o.id === id)) {
                alert('Obat ini sudah dipilih.');
                this.value = '';
                return;
            }

            selectedObats.push({ id, nama, harga });
            renderSelectedObats();
            this.value = '';
        });

        function removeObat(id) {
            selectedObats = selectedObats.filter(o => o.id !== id);
            renderSelectedObats();
        }

        function renderSelectedObats() {
            const container = document.getElementById('obat_terpilih_container');
            const totalDisplay = document.getElementById('total_harga_display');
            const inputJson = document.getElementById('obat_json');
            const inputBiaya = document.getElementById('biaya_periksa');

            container.innerHTML = '';

            if (selectedObats.length === 0) {
                container.innerHTML = '<span id="empty_notice" class="text-xs text-slate-400 italic">Belum ada obat yang dipilih</span>';
                totalDisplay.textContent = 'Rp 0';
                inputBiaya.value = 0;
                inputJson.value = '[]';
                return;
            }

            let total = 0;
            selectedObats.forEach(obat => {
                total += obat.harga;
                const chip = document.createElement('span');
                chip.className = 'inline-flex items-center gap-2 px-3 py-1 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-full text-xs font-semibold';
                chip.innerHTML = `${obat.nama} - Rp ${obat.harga.toLocaleString('id-ID')} <button type="button" onclick="removeObat(${obat.id})" class="text-indigo-400 hover:text-red-500 font-bold ml-1 cursor-pointer">&times;</button>`;
                container.appendChild(chip);
            });

            totalDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');
            inputBiaya.value = total;
            inputJson.value = JSON.stringify(selectedObats.map(o => o.id));
        }
    </script>
    @endpush

</x-layouts.app>
