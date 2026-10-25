<x-layouts.app title="Daftar Poli">

    <div style="display: flex; justify-content: center; align-items: center; min-height: calc(100vh - 170px); padding: 20px 16px; box-sizing: border-box;">

        <div style="width: 100%; max-width: 490px;">

            {{-- Alert session --}}
            @if(session('message'))
            <div class="alert {{ session('type') == 'success' ? 'alert-success' : 'alert-error' }} mb-3 rounded-lg shadow-sm" style="padding: 10px 14px; font-size: 12.5px; margin-bottom: 14px;">
                <i class="fas {{ session('type') == 'success' ? 'fa-check-circle' : 'fa-circle-xmark' }}"></i>
                <span>{{ session('message') }}</span>
            </div>
            @endif

            {{-- Card Box --}}
            <div style="background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05); padding: 28px 32px; box-sizing: border-box;">

                {{-- Judul --}}
                <h2 style="font-size: 19px; font-weight: 700; color: #1e293b; text-align: center; margin-top: 0; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <span style="font-size: 18px;">📑</span> Pendaftaran Poli
                </h2>

                <form action="{{ route('pasien.daftar.submit') }}" method="POST" style="display: flex; flex-direction: column; gap: 13px; margin: 0;">
                    @csrf
                    <input type="hidden" name="id_pasien" value="{{ $user->id }}">

                    {{-- Nomor Rekam Medis --}}
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #1e293b; margin-bottom: 5px;">
                            Nomor Rekam Medis
                        </label>
                        <input type="text" value="{{ $user->no_rm ?? '-' }}" readonly
                            style="width: 100%; background-color: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 7px; padding: 8px 12px; font-size: 12.5px; color: #475569; outline: none; box-sizing: border-box; cursor: not-allowed;">
                    </div>

                    {{-- Pilih Poli --}}
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #1e293b; margin-bottom: 5px;">
                            Pilih Poli
                        </label>
                        <select name="id_poli" id="id_poli" required
                            style="width: 100%; background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 7px; padding: 8px 12px; font-size: 12.5px; color: #64748b; outline: none; box-sizing: border-box; cursor: pointer;">
                            <option value="" disabled selected>-- Pilih Poli --</option>
                            @foreach($polis as $poli)
                                <option value="{{ $poli->id }}">{{ $poli->nama_poli }}</option>
                            @endforeach
                        </select>
                        @error('id_poli')
                            <p style="color: #ef4444; font-size: 11px; margin-top: 3px; margin-bottom: 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Pilih Jadwal Periksa --}}
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #1e293b; margin-bottom: 5px;">
                            Pilih Jadwal Periksa
                        </label>
                        <select name="id_jadwal" id="id_jadwal" required
                            style="width: 100%; background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 7px; padding: 8px 12px; font-size: 12.5px; color: #64748b; outline: none; box-sizing: border-box; cursor: pointer;">
                            <option value="" disabled selected>-- Pilih Jadwal --</option>
                            @foreach($jadwals as $jadwal)
                                <option value="{{ $jadwal->id }}" data-poli="{{ $jadwal->dokter->id_poli ?? '' }}">
                                    {{ $jadwal->dokter->nama ?? 'Dokter' }} - {{ $jadwal->hari }} ({{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_jadwal')
                            <p style="color: #ef4444; font-size: 11px; margin-top: 3px; margin-bottom: 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Keluhan --}}
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #1e293b; margin-bottom: 5px;">
                            Keluhan
                        </label>
                        <textarea name="keluhan" rows="3" placeholder="Tulis keluhan anda..."
                            style="width: 100%; background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 7px; padding: 8px 12px; font-size: 12.5px; color: #1e293b; outline: none; box-sizing: border-box; resize: vertical; height: 75px; min-height: 65px;">{{ old('keluhan') }}</textarea>
                        @error('keluhan')
                            <p style="color: #ef4444; font-size: 11px; margin-top: 3px; margin-bottom: 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tombol Submit --}}
                    <div style="display: flex; justify-content: flex-end; margin-top: 6px;">
                        <button type="submit"
                            style="background-color: #1d4ed8 !important; color: #ffffff !important; border: none; border-radius: 7px; padding: 8px 20px; font-size: 12.5px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; line-height: 1.4; transition: background-color 0.15s ease-in-out;"
                            onmouseover="this.style.backgroundColor='#1e40af'"
                            onmouseout="this.style.backgroundColor='#1d4ed8'">
                            Daftar Poli
                        </button>
                    </div>
                </form>

            </div>

        </div>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectPoli = document.getElementById('id_poli');
            const selectJadwal = document.getElementById('id_jadwal');
            const originalOptions = Array.from(selectJadwal.querySelectorAll('option'));

            function updateSelectColors() {
                selectPoli.style.color = selectPoli.value ? '#1e293b' : '#64748b';
                selectJadwal.style.color = selectJadwal.value ? '#1e293b' : '#64748b';
            }

            selectPoli.addEventListener('change', function () {
                const selectedPoliId = this.value;
                selectJadwal.innerHTML = '';

                const defaultOption = originalOptions[0].cloneNode(true);
                selectJadwal.appendChild(defaultOption);

                let matchCount = 0;
                originalOptions.slice(1).forEach(opt => {
                    const poliId = opt.getAttribute('data-poli');
                    if (!selectedPoliId || poliId === selectedPoliId) {
                        selectJadwal.appendChild(opt.cloneNode(true));
                        matchCount++;
                    }
                });

                if (matchCount === 0 && selectedPoliId) {
                    const emptyOpt = document.createElement('option');
                    emptyOpt.value = '';
                    emptyOpt.disabled = true;
                    emptyOpt.textContent = 'Belum ada dokter/jadwal untuk poli ini';
                    selectJadwal.appendChild(emptyOpt);
                }

                selectJadwal.value = '';
                updateSelectColors();
            });

            selectJadwal.addEventListener('change', function () {
                const selectedOpt = this.options[this.selectedIndex];
                const poliId = selectedOpt.getAttribute('data-poli');
                if (poliId && !selectPoli.value) {
                    selectPoli.value = poliId;
                }
                updateSelectColors();
            });

            updateSelectColors();
        });
    </script>
    @endpush

</x-layouts.app>
