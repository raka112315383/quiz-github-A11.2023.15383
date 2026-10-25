<?php

namespace Tests\Feature;

use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\JadwalPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatPasienTest extends TestCase
{
    use RefreshDatabase;

    public function test_dokter_can_access_riwayat_pasien_index_and_detail(): void
    {
        $poli = Poli::create([
            'nama_poli' => 'Poli Umum',
            'keterangan' => 'Poli Umum Test',
        ]);

        $dokter = User::create([
            'nama' => 'Dr. Ahmad Rahman, Sp. PD',
            'email' => 'dokter@test.com',
            'password' => bcrypt('password'),
            'role' => 'dokter',
            'id_poli' => $poli->id,
        ]);

        $pasien = User::create([
            'nama' => 'titi',
            'email' => 'pasien@test.com',
            'password' => bcrypt('password'),
            'role' => 'pasien',
            'no_rm' => '202610-001',
        ]);

        $jadwal = JadwalPeriksa::create([
            'id_dokter' => $dokter->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
        ]);

        $daftarPoli = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'keluhan' => 'sakit demam',
            'no_antrian' => 1,
        ]);

        $obat = Obat::create([
            'nama_obat' => 'Paracetamol',
            'kemasan' => 'Strip',
            'harga' => 20000,
        ]);

        $periksa = Periksa::create([
            'id_daftar_poli' => $daftarPoli->id,
            'tgl_periksa' => now(),
            'catatan' => 'hdhdidy',
            'biaya_periksa' => 170000,
        ]);

        DetailPeriksa::create([
            'id_periksa' => $periksa->id,
            'id_obat' => $obat->id,
        ]);

        // Test index
        $indexResponse = $this->actingAs($dokter)->get(route('riwayat-pasien.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Riwayat Pasien');
        $indexResponse->assertSee('titi');
        $indexResponse->assertSee('sakit demam');
        $indexResponse->assertSee('Rp 170.000');

        // Test detail
        $detailResponse = $this->actingAs($dokter)->get(route('riwayat-pasien.detail', $periksa->id));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Detail Riwayat');
        $detailResponse->assertSee('titi');
        $detailResponse->assertSee('Paracetamol');
        $detailResponse->assertSee('Rp 170.000');
    }
}
