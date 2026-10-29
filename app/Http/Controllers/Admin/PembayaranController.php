<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Periksa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    /**
     * Tampilkan daftar tagihan pemeriksaan yang menunggu verifikasi admin atau sesuai filter.
     */
    public function index(Request $request): View
    {
        $status = $request->get('status', 'menunggu_verifikasi');

        $query = Periksa::with([
            'daftarPoli.pasien',
            'daftarPoli.jadwalPeriksa.dokter.poli',
            'detailPeriksas.obat'
        ])->orderBy('id', 'desc');

        if ($status !== 'semua') {
            $query->where('status_pembayaran', $status);
        }

        $tagihans = $query->get();

        // Hitung total untuk badge status
        $countMenunggu = Periksa::where('status_pembayaran', 'menunggu_verifikasi')->count();
        $countLunas = Periksa::where('status_pembayaran', 'lunas')->count();
        $countBelumBayar = Periksa::where('status_pembayaran', 'belum_bayar')->count();
        $countSemua = Periksa::count();

        return view('admin.pembayaran.index', compact(
            'tagihans',
            'status',
            'countMenunggu',
            'countLunas',
            'countBelumBayar',
            'countSemua'
        ));
    }

    /**
     * Konfirmasi pembayaran pasien menjadi lunas setelah admin memeriksa bukti transfer.
     */
    public function konfirmasi(Request $request, int|string $id): RedirectResponse
    {
        $periksa = Periksa::with('daftarPoli.pasien')->findOrFail($id);

        $periksa->update([
            'status_pembayaran' => 'lunas',
            'tgl_konfirmasi' => now(),
        ]);

        $namaPasien = $periksa->daftarPoli->pasien->nama ?? 'Pasien';

        // Kirim notifikasi ke pasien bahwa pembayaran telah lunas (anti-duplikasi)
        $pasien = $periksa->daftarPoli?->pasien;
        if ($pasien) {
            $alreadyNotified = $pasien->notifications()
                ->where('type', \App\Notifications\PembayaranDiverifikasiNotification::class)
                ->where('data->periksa_id', $periksa->id)
                ->exists();

            if (!$alreadyNotified) {
                $pasien->notify(new \App\Notifications\PembayaranDiverifikasiNotification($periksa));
            }
        }

        return redirect()
            ->back()
            ->with('success', "Pembayaran untuk {$namaPasien} berhasil dikonfirmasi! Status tagihan kini Lunas.");
    }
}
