<?php

namespace App\Notifications;

use App\Models\Periksa;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BuktiPembayaranDiunggahNotification extends Notification
{
    use Queueable;

    public Periksa $periksa;

    /**
     * Create a new notification instance.
     */
    public function __construct(Periksa $periksa)
    {
        $this->periksa = $periksa;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $pasienNama = $this->periksa->daftarPoli->pasien->nama ?? 'Pasien';
        $poli = $this->periksa->daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? 'Poli';
        $biaya = number_format($this->periksa->biaya_periksa, 0, ',', '.');

        return [
            'type' => 'bukti_pembayaran_diunggah',
            'title' => 'Pasien Mengunggah Bukti Pembayaran',
            'message' => "Pasien {$pasienNama} telah mengunggah bukti pembayaran untuk pemeriksaan {$poli} (Rp {$biaya}).",
            'periksa_id' => $this->periksa->id,
            'url' => route('riwayat-pasien.detail', $this->periksa->id),
            'icon' => 'fa-receipt text-indigo-500',
        ];
    }
}
