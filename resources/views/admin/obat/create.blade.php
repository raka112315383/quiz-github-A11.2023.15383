<x-layouts.app title="Tambah Obat">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('obat.index') }}" class="flex items-center justify-center w-9 h-9 rounded-lg 
                  bg-slate-100 hover:bg-slate-200 
                  text-slate-600 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>

        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Tambah Obat Baru
            </h2>
            <p class="text-sm text-slate-500">Masukkan rincian data obat yang ingin didaftarkan</p>
        </div>
    </div>

    {{-- Card Form --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body p-8">

            <form action="{{ route('obat.store') }}" method="POST">
                @csrf

                <div class="space-y-6">

                    {{-- Nama Obat --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Nama Obat <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama_obat" value="{{ old('nama_obat') }}"
                            placeholder="Contoh: Paracetamol 500mg, Amoxicillin..." 
                            class="w-full px-4 py-2.5 rounded-lg border-2 border-slate-300 
                                   focus:border-primary focus:outline-none transition
                                   @error('nama_obat') border-red-500 @enderror"
                            required>
                        @error('nama_obat')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kemasan --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Kemasan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="kemasan" value="{{ old('kemasan') }}"
                            placeholder="Contoh: Strip (10 Tablet), Botol 60ml, Kapsul..." 
                            class="w-full px-4 py-2.5 rounded-lg border-2 border-slate-300 
                                   focus:border-primary focus:outline-none transition
                                   @error('kemasan') border-red-500 @enderror"
                            required>
                        @error('kemasan')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Stok --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Stok <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="stok" value="{{ old('stok', 0) }}" min="0" step="1"
                            placeholder="Contoh: 100" 
                            class="w-full px-4 py-2.5 rounded-lg border-2 border-slate-300 
                                   focus:border-primary focus:outline-none transition
                                   @error('stok') border-red-500 @enderror"
                            required>
                        @error('stok')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Harga --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Harga (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 font-semibold text-sm">
                                Rp
                            </span>
                            <input type="number" name="harga" value="{{ old('harga') }}" min="0" step="100"
                                placeholder="Contoh: 15000" 
                                class="w-full pl-12 pr-4 py-2.5 rounded-lg border-2 border-slate-300 
                                       focus:border-primary focus:outline-none transition
                                       @error('harga') border-red-500 @enderror"
                                required>
                        </div>
                        @error('harga')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- Buttons --}}
                <div class="flex items-center gap-3 mt-8 pt-6 border-t border-slate-100">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary hover:bg-primary/90 
                               text-white font-semibold text-sm transition shadow-sm">
                        <i class="fas fa-save text-xs"></i>
                        Simpan Obat
                    </button>

                    <a href="{{ route('obat.index') }}"
                        class="px-6 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 
                               text-slate-600 font-semibold text-sm transition">
                        Batal
                    </a>
                </div>

            </form>

        </div>
    </div>

</x-layouts.app>
