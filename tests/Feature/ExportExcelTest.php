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
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExportExcelTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'nama' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
    }

    private function createDokter(string $email = 'dokter@test.com', ?int $poliId = null): User
    {
        return User::create([
            'nama' => 'dr. ' . fake()->name(),
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => 'dokter',
            'no_ktp' => '3301010101010001',
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Mawar No. 10',
            'id_poli' => $poliId,
        ]);
    }

    private function createPasien(string $email = 'pasien@test.com'): User
    {
        return User::create([
            'nama' => 'Pasien ' . fake()->name(),
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => 'pasien',
            'no_rm' => '202610-001',
            'no_ktp' => '3301010101010002',
            'no_hp' => '089876543210',
            'alamat' => 'Jl. Melati No. 5',
        ]);
    }

    public function test_guest_cannot_access_export_routes()
    {
        $this->get(route('dokter.export'))->assertRedirect('/login');
        $this->get(route('pasien.export'))->assertRedirect('/login');
        $this->get(route('obat.export'))->assertRedirect('/login');
        $this->get(route('jadwal-periksa.export'))->assertRedirect('/login');
        $this->get(route('riwayat-pasien.export'))->assertRedirect('/login');
    }

    public function test_pasien_cannot_access_admin_or_dokter_export()
    {
        $pasien = $this->createPasien();

        $this->actingAs($pasien)->get(route('dokter.export'))->assertForbidden();
        $this->actingAs($pasien)->get(route('pasien.export'))->assertForbidden();
        $this->actingAs($pasien)->get(route('obat.export'))->assertForbidden();
        $this->actingAs($pasien)->get(route('jadwal-periksa.export'))->assertForbidden();
        $this->actingAs($pasien)->get(route('riwayat-pasien.export'))->assertForbidden();
    }

    public function test_admin_can_export_dokter()
    {
        $admin = $this->createAdmin();
        $poli = Poli::create(['nama_poli' => 'Poli Mata', 'keterangan' => 'Spesialis Mata']);
        $dokter = $this->createDokter('mata@test.com', $poli->id);

        $response = $this->actingAs($admin)->get(route('dokter.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
        $response->assertHeader('Content-Disposition');
        $this->assertStringContainsString('Poli Mata', $response->getContent());
        $this->assertStringContainsString($dokter->nama, $response->getContent());
        $this->assertStringContainsString($dokter->no_ktp, $response->getContent());
    }

    public function test_admin_can_export_pasien()
    {
        $admin = $this->createAdmin();
        $pasien = $this->createPasien();

        $response = $this->actingAs($admin)->get(route('pasien.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
        $this->assertStringContainsString($pasien->nama, $response->getContent());
        $this->assertStringContainsString($pasien->no_rm, $response->getContent());
        $this->assertStringContainsString($pasien->no_ktp, $response->getContent());
    }

    public function test_admin_can_export_obat_with_stok_and_harga()
    {
        $admin = $this->createAdmin();
        $obat = Obat::create([
            'nama_obat' => 'Amoxicillin 500mg',
            'kemasan' => 'Strip 10 Kaplet',
            'harga' => 15000,
            'stok' => 75,
        ]);

        $response = $this->actingAs($admin)->get(route('obat.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
        $this->assertStringContainsString('Amoxicillin 500mg', $response->getContent());
        $this->assertStringContainsString('Strip 10 Kaplet', $response->getContent());
        $this->assertStringContainsString('75', $response->getContent());
        $this->assertStringContainsString('15.000', $response->getContent());
    }

    public function test_admin_can_store_and_update_obat_with_stok()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('obat.store'), [
            'nama_obat' => 'Paracetamol 500mg',
            'kemasan' => 'Strip',
            'harga' => 5000,
            'stok' => 120,
        ]);

        $response->assertRedirect(route('obat.index'));
        $this->assertDatabaseHas('obat', [
            'nama_obat' => 'Paracetamol 500mg',
            'stok' => 120,
            'harga' => 5000,
        ]);

        $obat = Obat::where('nama_obat', 'Paracetamol 500mg')->first();

        $responseUpdate = $this->actingAs($admin)->put(route('obat.update', $obat->id), [
            'nama_obat' => 'Paracetamol 500mg Forte',
            'kemasan' => 'Strip',
            'harga' => 6000,
            'stok' => 150,
        ]);

        $responseUpdate->assertRedirect(route('obat.index'));
        $this->assertDatabaseHas('obat', [
            'id' => $obat->id,
            'nama_obat' => 'Paracetamol 500mg Forte',
            'stok' => 150,
        ]);
    }

    public function test_dokter_can_export_only_their_own_jadwal_periksa()
    {
        $poli = Poli::create(['nama_poli' => 'Poli Gigi', 'keterangan' => 'Spesialis Gigi']);
        $dokter1 = $this->createDokter('dokter1@test.com', $poli->id);
        $dokter2 = $this->createDokter('dokter2@test.com', $poli->id);

        $jadwal1 = JadwalPeriksa::create([
            'id_dokter' => $dokter1->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
        ]);

        $jadwal2 = JadwalPeriksa::create([
            'id_dokter' => $dokter2->id,
            'hari' => 'Kamis',
            'jam_mulai' => '13:00',
            'jam_selesai' => '17:00',
        ]);

        $response = $this->actingAs($dokter1)->get(route('jadwal-periksa.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
        $this->assertStringContainsString('Senin', $response->getContent());
        $this->assertStringNotContainsString('Kamis', $response->getContent());
    }

    public function test_dokter_can_export_only_their_own_riwayat_pasien()
    {
        $poli = Poli::create(['nama_poli' => 'Poli Penyakit Dalam', 'keterangan' => 'Spesialis PD']);
        $dokter1 = $this->createDokter('dokter1_riwayat@test.com', $poli->id);
        $dokter2 = $this->createDokter('dokter2_riwayat@test.com', $poli->id);
        $pasien1 = $this->createPasien('pasien1_riwayat@test.com');
        $pasien2 = $this->createPasien('pasien2_riwayat@test.com');

        $jadwal1 = JadwalPeriksa::create([
            'id_dokter' => $dokter1->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
        ]);

        $jadwal2 = JadwalPeriksa::create([
            'id_dokter' => $dokter2->id,
            'hari' => 'Rabu',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
        ]);

        $daftar1 = DaftarPoli::create([
            'id_pasien' => $pasien1->id,
            'id_jadwal' => $jadwal1->id,
            'keluhan' => 'Demam tinggi dokter 1',
            'no_antrian' => 1,
        ]);

        $daftar2 = DaftarPoli::create([
            'id_pasien' => $pasien2->id,
            'id_jadwal' => $jadwal2->id,
            'keluhan' => 'Sakit kepala dokter 2',
            'no_antrian' => 1,
        ]);

        $obat = Obat::create([
            'nama_obat' => 'Paracetamol Forte',
            'kemasan' => 'Strip',
            'harga' => 10000,
            'stok' => 50,
        ]);

        $periksa1 = Periksa::create([
            'id_daftar_poli' => $daftar1->id,
            'tgl_periksa' => '2026-10-27 10:00:00',
            'catatan' => 'Istirahat yang cukup',
            'biaya_periksa' => 160000,
            'status_pembayaran' => 'belum_bayar',
        ]);

        DetailPeriksa::create([
            'id_periksa' => $periksa1->id,
            'id_obat' => $obat->id,
        ]);

        $periksa2 = Periksa::create([
            'id_daftar_poli' => $daftar2->id,
            'tgl_periksa' => '2026-10-27 11:00:00',
            'catatan' => 'Minum air hangat',
            'biaya_periksa' => 150000,
            'status_pembayaran' => 'belum_bayar',
        ]);

        $response = $this->actingAs($dokter1)->get(route('riwayat-pasien.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
        $this->assertStringContainsString('Demam tinggi dokter 1', $response->getContent());
        $this->assertStringContainsString('Paracetamol Forte', $response->getContent());
        $this->assertStringContainsString('160.000', $response->getContent());
        $this->assertStringNotContainsString('Sakit kepala dokter 2', $response->getContent());
    }
}
