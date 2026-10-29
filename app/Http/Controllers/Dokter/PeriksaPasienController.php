<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeriksaPasienController extends Controller
{
    public function index()
    {
        $dokterId = Auth::id();

        $daftarPasien = DaftarPoli::with(['pasien', 'jadwalPeriksa', 'periksas'])
            ->whereHas('jadwalPeriksa', function ($query) use ($dokterId) {
                $query->where('id_dokter', $dokterId);
            })
            ->orderBy('no_antrian')
            ->get();

        return view('dokter.periksa-pasien.index', compact('daftarPasien'));
    }

    public function create($id = null)
    {
        $id = $id ?? (DaftarPoli::first()->id ?? 1);
        $obats = Obat::all();
        return view('dokter.periksa-pasien.create', compact('obats', 'id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'obat_json' => 'required',
            'catatan' => 'nullable|string',
            'biaya_periksa' => 'required|integer',
        ]);

        $obatIds = json_decode($request->obat_json, true);

        $periksa = Periksa::create([
            'id_daftar_poli' => $request->id_daftar_poli,
            'tgl_periksa' => now(),
            'catatan' => $request->catatan,
            'biaya_periksa' => $request->biaya_periksa + 150000,
        ]);

        if (is_array($obatIds)) {
            foreach ($obatIds as $idObat) {
                DetailPeriksa::create([
                    'id_periksa' => $periksa->id,
                    'id_obat' => $idObat,
                ]);
            }
        }

        // Ambil info daftar poli untuk broadcast
        $daftarPoli = DaftarPoli::with('jadwalPeriksa.dokter.poli')->find($request->id_daftar_poli);
        if ($daftarPoli) {
            $idPoli = $daftarPoli->jadwalPeriksa->dokter->poli->id ?? null;
            $namaPoli = $daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? 'Poli';

            // Bersihkan cache dipanggil jika antrian ini selesai
            if ($idPoli) {
                cache()->forget("poli_sedang_dipanggil_{$idPoli}");
            }
            cache()->forget("jadwal_sedang_dipanggil_{$daftarPoli->id_jadwal}");

            // Cari antrian berikutnya yang belum diperiksa
            $nextAntrian = DaftarPoli::where('id_jadwal', $daftarPoli->id_jadwal)
                ->doesntHave('periksas')
                ->orderBy('no_antrian', 'asc')
                ->first();

            // Broadcast real-time update via Laravel Reverb
            \App\Events\AntrianUpdatedEvent::dispatch(
                $idPoli,
                $namaPoli,
                $daftarPoli->id_jadwal,
                $nextAntrian?->no_antrian,
                $nextAntrian?->pasien->nama,
                $daftarPoli->jadwalPeriksa->dokter->nama ?? 'Dokter',
                'selesai',
                $daftarPoli->id_pasien
            );
        }

        return redirect()
            ->route('periksa-pasien.index')
            ->with('success', 'Data periksa berhasil disimpan.');
    }

    /**
     * Dokter memanggil pasien ke ruang periksa.
     * Mengirim event real-time ke WebSocket server Laravel Reverb.
     */
    public function panggil($id)
    {
        $daftar = DaftarPoli::with(['pasien', 'jadwalPeriksa.dokter.poli'])->findOrFail($id);

        $poli = $daftar->jadwalPeriksa->dokter->poli ?? null;
        $idPoli = $poli ? $poli->id : null;
        $namaPoli = $poli ? $poli->nama_poli : 'Poli';

        // Simpan status sedang dipanggil ke Cache agar konsisten di semua tampilan
        if ($idPoli) {
            cache()->put("poli_sedang_dipanggil_{$idPoli}", $daftar->no_antrian, now()->addHours(8));
        }
        cache()->put("jadwal_sedang_dipanggil_{$daftar->id_jadwal}", $daftar->no_antrian, now()->addHours(8));

        // Broadcast real-time event ke Laravel Reverb via WebSocket
        \App\Events\AntrianUpdatedEvent::dispatch(
            $idPoli,
            $namaPoli,
            $daftar->id_jadwal,
            $daftar->no_antrian,
            $daftar->pasien->nama ?? 'Pasien',
            $daftar->jadwalPeriksa->dokter->nama ?? 'Dokter',
            'dipanggil',
            $daftar->id_pasien
        );

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Antrian #' . $daftar->no_antrian . ' (' . ($daftar->pasien->nama ?? 'Pasien') . ') berhasil dipanggil!',
                'no_antrian' => $daftar->no_antrian,
                'poli' => $namaPoli,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Nomor Antrian #' . $daftar->no_antrian . ' (' . ($daftar->pasien->nama ?? 'Pasien') . ') berhasil dipanggil!');
    }
}