<?php

namespace App\Notifications;

use App\Models\Periksa;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PembayaranDiverifikasiNotification extends Notification
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
        $poli = $this->periksa->daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? 'Poli';
        $biaya = number_format($this->periksa->biaya_periksa, 0, ',', '.');

        return [
            'type' => 'pembayaran_diverifikasi',
            'title' => 'Pembayaran Berhasil Diverifikasi',
            'message' => "Pembayaran pemeriksaan {$poli} sebesar Rp {$biaya} telah diverifikasi dan dinyatakan Lunas oleh admin.",
            'periksa_id' => $this->periksa->id,
            'url' => route('pasien.pembayaran.index'),
            'icon' => 'fa-circle-check text-emerald-500',
        ];
    }
}
