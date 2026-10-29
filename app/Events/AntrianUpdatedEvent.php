<?php

namespace App\Events;

use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Poli;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AntrianUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ?int $idPoli;
    public ?string $namaPoli;
    public ?int $idJadwal;
    public ?int $noAntrian;
    public ?string $namaPasien;
    public ?string $namaDokter;
    public string $aksi; // 'dipanggil', 'selesai', 'didaftar'
    public ?int $idPasien;
    public array $jadwals;
    public array $polis;
    public string $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(
        ?int $idPoli = null,
        ?string $namaPoli = null,
        ?int $idJadwal = null,
        ?int $noAntrian = null,
        ?string $namaPasien = null,
        ?string $namaDokter = null,
        string $aksi = 'dipanggil',
        ?int $idPasien = null
    ) {
        $this->idPoli = $idPoli;
        $this->namaPoli = $namaPoli;
        $this->idJadwal = $idJadwal;
        $this->noAntrian = $noAntrian;
        $this->namaPasien = $namaPasien;
        $this->namaDokter = $namaDokter;
        $this->aksi = $aksi;
        $this->idPasien = $idPasien;
        $this->timestamp = now()->toIso8601String();

        // Ambil data real-time seluruh jadwal dan nomor antrian yang sedang dipanggil di setiap poli
        $this->jadwals = $this->buildJadwalsData();
        $this->polis = $this->buildPolisData();
    }

    /**
     * The channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('antrian'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'AntrianUpdated';
    }

    /**
     * Ambil data nomor sedang dilayani per jadwal.
     */
    protected function buildJadwalsData(): array
    {
        $sedangDilayani = DaftarPoli::doesntHave('periksas')
            ->selectRaw('id_jadwal, MIN(no_antrian) as sedang_dilayani')
            ->groupBy('id_jadwal')
            ->pluck('sedang_dilayani', 'id_jadwal')
            ->toArray();

        $result = [];
        $jadwals = JadwalPeriksa::select('id')->get();
        foreach ($jadwals as $j) {
            $cached = cache()->get("jadwal_sedang_dipanggil_{$j->id}");
            $val = $cached ?: ($sedangDilayani[$j->id] ?? null);

            $result[] = [
                'id' => $j->id,
                'sedang_dilayani' => $val,
            ];
        }

        return $result;
    }

    /**
     * Ambil data nomor antrian yang sedang dipanggil di setiap poli.
     */
    protected function buildPolisData(): array
    {
        $polis = Poli::all();
        $result = [];

        foreach ($polis as $poli) {
            $cached = cache()->get("poli_sedang_dipanggil_{$poli->id}");

            if ($cached) {
                $nomorPoli = $cached;
            } else {
                $nomorPoli = DaftarPoli::whereHas('jadwalPeriksa.dokter', function ($q) use ($poli) {
                    $q->where('id_poli', $poli->id);
                })
                ->doesntHave('periksas')
                ->orderBy('no_antrian', 'asc')
                ->value('no_antrian');
            }

            $result[] = [
                'id' => $poli->id,
                'nama_poli' => $poli->nama_poli,
                'sedang_dipanggil' => $nomorPoli,
            ];
        }

        return $result;
    }
}
