<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PoliController extends Controller
{
    public function get()
    {
        $user = Auth::user();
        $polis = Poli::all();
        $jadwal = JadwalPeriksa::with('dokter', 'dokter.poli')->get();

        // Cek apakah pasien masih memiliki antrian aktif (belum selesai diperiksa)
        $antrianAktif = DaftarPoli::with(['jadwalPeriksa.dokter.poli'])
            ->where('id_pasien', $user->id)
            ->doesntHave('periksas')
            ->first();

        return view('pasien.daftar', [
            'user' => $user,
            'polis' => $polis,
            'jadwals' => $jadwal,
            'antrianAktif' => $antrianAktif,
        ]);
    }

    public function submit(Request $request)
    {
        $user = Auth::user();
        $pasienId = $user ? $user->id : $request->id_pasien;

        // Ketentuan: Satu pasien tidak bisa mendaftar lebih dari satu kali ketika belum selesai
        // Pasien tidak dapat mendaftar ke dua poli sekaligus
        // Pasien hanya dapat mendaftar kembali setelah proses pemeriksaan selesai
        $antrianAktif = DaftarPoli::with(['jadwalPeriksa.dokter.poli'])
            ->where('id_pasien', $pasienId)
            ->whereDoesntHave('periksas')
            ->first();

        if ($antrianAktif) {
            $namaPoli = $antrianAktif->jadwalPeriksa->dokter->poli->nama_poli ?? 'Poli';
            $pesan = 'Satu pasien tidak bisa mendaftar lebih dari satu kali ketika belum selesai diperiksa. Anda masih memiliki antrian aktif di ' . $namaPoli . ' (No. Antrian #' . $antrianAktif->no_antrian . ').';
            return redirect()
                ->route('pasien.dashboard')
                ->with('message', $pesan)
                ->with('error', $pesan)
                ->with('type', 'error');
        }

        $request->validate([
            'id_poli' => 'required|exists:poli,id',
            'id_jadwal' => 'required|exists:jadwal_periksa,id',
            'keluhan' => 'nullable|string',
            'id_pasien' => 'required|exists:users,id',
        ]);

        // Gunakan Database Transaction dengan Lock untuk mencegah race condition
        $daftarBaru = DB::transaction(function () use ($request, $pasienId) {
            $sudahAda = DaftarPoli::where('id_pasien', $pasienId)
                ->whereDoesntHave('periksas')
                ->lockForUpdate()
                ->first();

            if ($sudahAda) {
                return null;
            }

            $jumlahSudahDaftar = DaftarPoli::where('id_jadwal', $request->id_jadwal)
                ->lockForUpdate()
                ->count();
            $noAntrian = $jumlahSudahDaftar + 1;

            return DaftarPoli::create([
                'id_pasien' => $pasienId,
                'id_jadwal' => $request->id_jadwal,
                'keluhan' => $request->keluhan,
                'no_antrian' => $noAntrian,
            ]);
        });

        if (!$daftarBaru) {
            $pesan = 'Satu pasien tidak bisa mendaftar lebih dari satu kali ketika belum selesai diperiksa.';
            return redirect()
                ->route('pasien.dashboard')
                ->with('message', $pesan)
                ->with('error', $pesan)
                ->with('type', 'error');
        }

        try {
            $poli = Poli::find($request->id_poli);
            \App\Events\AntrianUpdatedEvent::dispatch(
                $request->id_poli,
                $poli?->nama_poli ?? 'Poli',
                $request->id_jadwal,
                $daftarBaru->no_antrian,
                $user->nama ?? 'Pasien',
                null,
                'didaftar',
                $pasienId
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast AntrianUpdatedEvent failed (Reverb offline): ' . $e->getMessage());
        }

        return redirect()
            ->route('pasien.dashboard')
            ->with('message', 'Berhasil Mendaftar ke Poli! Nomor antrian Anda: #' . $daftarBaru->no_antrian)
            ->with('success', 'Berhasil Mendaftar ke Poli! Nomor antrian Anda: #' . $daftarBaru->no_antrian)
            ->with('type', 'success');
    }
}