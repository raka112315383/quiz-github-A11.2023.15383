<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RiwayatPendaftaranController extends Controller
{
    /**
     * Tampilkan riwayat semua pendaftaran poli milik pasien yang sedang login.
     * Eager loading digunakan secara optimal untuk efisiensi kueri agar tidak terjadi N+1 problem.
     */
    public function index(Request $request): View
    {
        $pasienId = Auth::id();

        // Mengambil seluruh pendaftaran pasien dengan eager loading jadwalPeriksa.dokter.poli dan periksa
        // Diurutkan dari yang terbaru (id desc)
        $riwayatPendaftaran = DaftarPoli::with([
            'jadwalPeriksa.dokter.poli',
            'periksa'
        ])
        ->where('id_pasien', $pasienId)
        ->orderBy('id', 'desc')
        ->get();

        return view('pasien.riwayat.index', compact('riwayatPendaftaran'));
    }

    /**
     * Tampilkan detail pemeriksaan pendaftaran jika sudah diperiksa.
     * Berisi catatan dokter, daftar obat yang diresepkan, dan total biaya.
     */
    public function detail(int|string $id, Request $request): View|RedirectResponse
    {
        $pasienId = Auth::id();

        // Eager load data pendaftaran milik pasien login beserta periksa & detail obat
        $daftarPoli = DaftarPoli::with([
            'pasien',
            'jadwalPeriksa.dokter.poli',
            'periksa.detailPeriksas.obat'
        ])
        ->where('id_pasien', $pasienId)
        ->findOrFail($id);

        // Jika belum diperiksa, redirect kembali dengan notifikasi
        if (!$daftarPoli->periksa) {
            return redirect()
                ->route('pasien.riwayat.index')
                ->with('error', 'Pemeriksaan untuk pendaftaran poli ini belum selesai dilakukan dokter.');
        }

        $periksa = $daftarPoli->periksa;

        return view('pasien.riwayat.detail', compact('daftarPoli', 'periksa'));
    }

    /**
     * Alias method show ke detail.
     */
    public function show(int|string $id, Request $request): View|RedirectResponse
    {
        return $this->detail($id, $request);
    }
}
