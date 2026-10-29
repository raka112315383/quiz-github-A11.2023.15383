<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Poli;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman Dashboard Pasien.
     */
    public function index(): View
    {
        $pasien = Auth::user();

        // Cari antrian aktif milik pasien (belum diperiksa oleh dokter)
        $antrianAktif = DaftarPoli::with(['jadwalPeriksa.dokter.poli'])
            ->where('id_pasien', $pasien->id)
            ->doesntHave('periksas')
            ->latest('id')
            ->first();

        $nomorSedangDilayani = null;
        if ($antrianAktif) {
            $nomorSedangDilayani = $this->getSedangDilayani($antrianAktif->id_jadwal);
        }

        // Ambil seluruh jadwal periksa dokter beserta polinya
        $jadwals = JadwalPeriksa::with(['dokter.poli'])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        // Ambil nomor yang sedang dilayani untuk setiap jadwal
        $sedangDilayaniPerJadwal = $this->getAllSedangDilayani();

        // Ambil status antrian yang sedang dipanggil di setiap poli
        $polisStatus = $this->getPolisStatus();

        $daftarPolis = DaftarPoli::with(['jadwalPeriksa.dokter.poli', 'periksas'])
            ->where('id_pasien', $pasien->id)
            ->latest('id')
            ->take(5)
            ->get();

        $totalKunjungan = DaftarPoli::where('id_pasien', $pasien->id)->count();
        $totalSelesai = DaftarPoli::where('id_pasien', $pasien->id)->has('periksas')->count();

        return view('pasien.dashboard', compact(
            'pasien',
            'antrianAktif',
            'nomorSedangDilayani',
            'jadwals',
            'sedangDilayaniPerJadwal',
            'polisStatus',
            'daftarPolis',
            'totalKunjungan',
            'totalSelesai'
        ));
    }

    /**
     * Endpoint API untuk live update status antrian & nomor yang sedang dilayani
     * Terupdate secara otomatis tanpa reload halaman via AJAX/fetch (sebagai fallback).
     */
    public function liveAntrian(): JsonResponse
    {
        $pasien = Auth::user();

        // Cek antrian aktif pasien
        $antrianAktif = DaftarPoli::with(['jadwalPeriksa.dokter.poli'])
            ->where('id_pasien', $pasien->id)
            ->doesntHave('periksas')
            ->latest('id')
            ->first();

        $antrianData = null;
        if ($antrianAktif) {
            $sedangDilayani = $this->getSedangDilayani($antrianAktif->id_jadwal);

            $jamMulai = substr($antrianAktif->jadwalPeriksa->jam_mulai, 0, 5);
            $jamSelesai = substr($antrianAktif->jadwalPeriksa->jam_selesai, 0, 5);

            $antrianData = [
                'id' => $antrianAktif->id,
                'poli' => $antrianAktif->jadwalPeriksa->dokter->poli->nama_poli ?? 'Poli',
                'id_poli' => $antrianAktif->jadwalPeriksa->dokter->poli->id ?? null,
                'dokter' => $antrianAktif->jadwalPeriksa->dokter->nama ?? 'Dokter',
                'hari' => $antrianAktif->jadwalPeriksa->hari,
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'jadwal_format' => $antrianAktif->jadwalPeriksa->hari . ' (' . $jamMulai . ' - ' . $jamSelesai . ')',
                'no_antrian' => $antrianAktif->no_antrian,
                'sedang_dilayani' => $sedangDilayani ?? '-',
                'is_your_turn' => ($sedangDilayani !== null && $sedangDilayani == $antrianAktif->no_antrian),
            ];
        }

        // Ambil data nomor sedang dilayani untuk seluruh jadwal
        $sedangDilayaniPerJadwal = $this->getAllSedangDilayani();
        $jadwalList = [];
        $allJadwals = JadwalPeriksa::select('id')->get();
        foreach ($allJadwals as $j) {
            $jadwalList[] = [
                'id' => $j->id,
                'sedang_dilayani' => $sedangDilayaniPerJadwal[$j->id] ?? null,
            ];
        }

        $polisStatus = $this->getPolisStatus();

        return response()->json([
            'has_active' => $antrianAktif !== null,
            'antrian_aktif' => $antrianData,
            'jadwals' => $jadwalList,
            'polis' => $polisStatus,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Hitung nomor antrian yang sedang dilayani untuk satu jadwal tertentu.
     */
    protected function getSedangDilayani(int $jadwalId): ?int
    {
        $cached = cache()->get("jadwal_sedang_dipanggil_{$jadwalId}");
        if ($cached) {
            return (int) $cached;
        }

        return DaftarPoli::where('id_jadwal', $jadwalId)
            ->doesntHave('periksas')
            ->orderBy('no_antrian', 'asc')
            ->value('no_antrian');
    }

    /**
     * Ambil seluruh nomor antrian yang sedang dilayani per jadwal dalam 1 kueri efisien.
     */
    protected function getAllSedangDilayani(): array
    {
        $sedang = DaftarPoli::doesntHave('periksas')
            ->selectRaw('id_jadwal, MIN(no_antrian) as sedang_dilayani')
            ->groupBy('id_jadwal')
            ->pluck('sedang_dilayani', 'id_jadwal')
            ->toArray();

        // Terapkan override cache jika ada dokter yang memanggil nomor tertentu
        $jadwals = JadwalPeriksa::select('id')->get();
        foreach ($jadwals as $j) {
            $cached = cache()->get("jadwal_sedang_dipanggil_{$j->id}");
            if ($cached) {
                $sedang[$j->id] = (int) $cached;
            }
        }

        return $sedang;
    }

    /**
     * Ambil nomor antrian yang sedang dipanggil di setiap poli.
     */
    public function getPolisStatus(): array
    {
        $polis = Poli::all();
        $result = [];

        foreach ($polis as $poli) {
            $cached = cache()->get("poli_sedang_dipanggil_{$poli->id}");

            if ($cached) {
                $nomorPoli = (int) $cached;
            } else {
                $nomorPoli = DaftarPoli::whereHas('jadwalPeriksa.dokter', function ($q) use ($poli) {
                    $q->where('id_poli', $poli->id);
                })
                ->doesntHave('periksas')
                ->orderBy('no_antrian', 'asc')
                ->value('no_antrian');
            }

            $totalMenunggu = DaftarPoli::whereHas('jadwalPeriksa.dokter', function ($q) use ($poli) {
                $q->where('id_poli', $poli->id);
            })
            ->doesntHave('periksas')
            ->count();

            $result[] = [
                'id' => $poli->id,
                'nama_poli' => $poli->nama_poli,
                'deskripsi' => $poli->deskripsi,
                'sedang_dipanggil' => $nomorPoli,
                'total_menunggu' => $totalMenunggu,
            ];
        }

        return $result;
    }
}
