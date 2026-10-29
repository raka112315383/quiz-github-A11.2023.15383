<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\Periksa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    /**
     * Tampilkan daftar tagihan pemeriksaan milik pasien yang sedang login.
     */
    public function index(Request $request): View
    {
        $pasienId = Auth::id();

        // Mengambil semua tagihan hasil pemeriksaan pasien yang sedang login dengan eager loading
        $tagihans = Periksa::with([
            'daftarPoli.jadwalPeriksa.dokter.poli',
            'detailPeriksas.obat'
        ])
        ->whereHas('daftarPoli', function ($query) use ($pasienId) {
            $query->where('id_pasien', $pasienId);
        })
        ->orderBy('id', 'desc')
        ->get();

        return view('pasien.pembayaran.index', compact('tagihans'));
    }

    /**
     * Upload foto bukti pembayaran untuk tagihan pemeriksaan tertentu.
     */
    public function upload(Request $request, int|string $id): RedirectResponse
    {
        $pasienId = Auth::id();

        // Cari periksa milik pasien yang sedang login
        $periksa = Periksa::whereHas('daftarPoli', function ($query) use ($pasienId) {
            $query->where('id_pasien', $pasienId);
        })->findOrFail($id);

        $request->validate([
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'catatan_pembayaran' => 'nullable|string|max:255',
        ], [
            'bukti_pembayaran.required' => 'Foto bukti pembayaran wajib diupload.',
            'bukti_pembayaran.image' => 'File bukti pembayaran harus berupa gambar.',
            'bukti_pembayaran.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg.',
            'bukti_pembayaran.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        // Hapus bukti pembayaran lama jika ada
        if ($periksa->bukti_pembayaran && Storage::disk('public')->exists($periksa->bukti_pembayaran)) {
            Storage::disk('public')->delete($periksa->bukti_pembayaran);
        }

        // Simpan file bukti pembayaran baru
        $path = $request->file('bukti_pembayaran')->store('bukti_pembayaran', 'public');

        $periksa->update([
            'bukti_pembayaran' => $path,
            'status_pembayaran' => 'menunggu_verifikasi',
            'tgl_bayar' => now(),
            'catatan_pembayaran' => $request->catatan_pembayaran,
        ]);

        // Kirim notifikasi ke dokter yang memeriksa pasien (anti-duplikasi)
        $periksa->load('daftarPoli.jadwalPeriksa.dokter');
        $dokter = $periksa->daftarPoli?->jadwalPeriksa?->dokter;

        if ($dokter) {
            $alreadyNotified = $dokter->unreadNotifications()
                ->where('type', \App\Notifications\BuktiPembayaranDiunggahNotification::class)
                ->where('data->periksa_id', $periksa->id)
                ->exists();

            if (!$alreadyNotified) {
                $dokter->notify(new \App\Notifications\BuktiPembayaranDiunggahNotification($periksa));
            }
        }

        return redirect()
            ->route('pasien.pembayaran.index')
            ->with('success', 'Bukti pembayaran berhasil diupload! Menunggu konfirmasi verifikasi dari admin.');
    }
}
