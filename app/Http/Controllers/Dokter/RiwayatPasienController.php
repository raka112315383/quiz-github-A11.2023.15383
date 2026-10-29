<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\Periksa;
use App\Services\ExcelExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiwayatPasienController extends Controller
{
    public function exportExcel()
    {
        $dokterId = Auth::id();

        $riwayatPasien = Periksa::with([
            'daftarPoli.pasien',
            'daftarPoli.jadwalPeriksa',
            'detailPeriksas.obat'
        ])
            ->whereHas('daftarPoli.jadwalPeriksa', function ($query) use ($dokterId) {
                $query->where('id_dokter', $dokterId);
            })
            ->orderBy('tgl_periksa', 'desc')
            ->get();

        $headers = [
            'No',
            'Tanggal Periksa',
            'Nama Pasien',
            'No. RM',
            'Keluhan',
            'Catatan Dokter',
            'Obat Diresepkan',
            'Biaya Periksa'
        ];

        $data = [];
        foreach ($riwayatPasien as $index => $periksa) {
            $obatList = $periksa->detailPeriksas->map(function ($dp) {
                return $dp->obat->nama_obat ?? null;
            })->filter()->implode(', ');

            $data[] = [
                $index + 1,
                $periksa->tgl_periksa ? date('d-m-Y', strtotime($periksa->tgl_periksa)) : '-',
                $periksa->daftarPoli->pasien->nama ?? '-',
                $periksa->daftarPoli->pasien->no_rm ?? '-',
                $periksa->daftarPoli->keluhan ?? '-',
                $periksa->catatan ?? '-',
                $obatList ?: '-',
                'Rp ' . number_format($periksa->biaya_periksa, 0, ',', '.')
            ];
        }

        return ExcelExportService::download('riwayat-pasien-' . date('Y-m-d'), 'Riwayat Pemeriksaan Pasien', $headers, $data);
    }

    public function index()
    {
        $dokterId = Auth::id();

        $riwayatPasien = Periksa::with(['daftarPoli.pasien', 'daftarPoli.jadwalPeriksa'])
            ->whereHas('daftarPoli.jadwalPeriksa', function ($query) use ($dokterId) {
                $query->where('id_dokter', $dokterId);
            })
            ->orderBy('tgl_periksa', 'desc')
            ->get();

        return view('dokter.riwayat-pasien.index', compact('riwayatPasien'));
    }

    public function detail($id)
    {
        $periksa = Periksa::with([
            'daftarPoli.pasien',
            'daftarPoli.jadwalPeriksa.dokter.poli',
            'detailPeriksas.obat'
        ])->findOrFail($id);

        return view('dokter.riwayat-pasien.detail', compact('periksa'));
    }

    public function show($id)
    {
        return $this->detail($id);
    }
}
