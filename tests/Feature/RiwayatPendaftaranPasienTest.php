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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RiwayatPendaftaranPasienTest extends TestCase
{
    use RefreshDatabase;

    private function createPasien(string $nama = 'Budi Pasien', string $email = 'budi@test.com'): User
    {
        return User::create([
            'nama' => $nama,
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => 'pasien',
            'no_rm' => '202610-001',
            'no_ktp' => '3301010101010001',
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Mawar No. 10',
        ]);
    }

    private function createDokterWithJadwal(string $namaPoli = 'Poli Umum', string $namaDokter = 'dr. Siti Rahma'): array
    {
        $poli = Poli::create([
            'nama_poli' => $namaPoli,
            'keterangan' => 'Keterangan ' . $namaPoli,
        ]);

        $dokter = User::create([
            'nama' => $namaDokter,
            'email' => strtolower(str_replace(' ', '', $namaDokter)) . '@test.com',
            'password' => Hash::make('password123'),
            'role' => 'dokter',
            'id_poli' => $poli->id,
        ]);

        $jadwal = JadwalPeriksa::create([
            'id_dokter' => $dokter->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
        ]);

        return compact('poli', 'dokter', 'jadwal');
    }

    public function test_guest_cannot_access_riwayat_pendaftaran(): void
    {
        $response = $this->get(route('pasien.riwayat.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_dokter_or_admin_cannot_access_pasien_riwayat(): void
    {
        $admin = User::create([
            'nama' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('pasien.riwayat.index'));
        $response->assertStatus(403);
    }

    public function test_pasien_can_view_riwayat_pendaftaran_with_correct_info_and_status(): void
    {
        $pasien = $this->createPasien();
        $dokterData = $this->createDokterWithJadwal('Poli Anak', 'dr. Citra Sp.A');

        // Pendaftaran 1: Belum diperiksa
        $daftar1 = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Demam dan batuk pilek',
            'no_antrian' => 1,
        ]);

        // Pendaftaran 2: Sudah diperiksa
        $daftar2 = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Sakit kepala berulang',
            'no_antrian' => 2,
        ]);

        $obat = Obat::create([
            'nama_obat' => 'Paracetamol Drops',
            'kemasan' => 'Botol 15ml',
            'harga' => 25000,
        ]);

        $periksa = Periksa::create([
            'id_daftar_poli' => $daftar2->id,
            'tgl_periksa' => now(),
            'catatan' => 'Pasien butuh istirahat cukup dan banyak minum air.',
            'biaya_periksa' => 175000,
        ]);

        DetailPeriksa::create([
            'id_periksa' => $periksa->id,
            'id_obat' => $obat->id,
        ]);

        $response = $this->actingAs($pasien)->get(route('pasien.riwayat.index'));

        $response->assertStatus(200);
        $response->assertSee('Riwayat Pendaftaran Poli');
        $response->assertSee('Poli Anak');
        $response->assertSee('dr. Citra Sp.A');
        $response->assertSee('Menunggu Pemeriksaan');
        $response->assertSee('Sudah Diperiksa');
        $response->assertSee(route('pasien.riwayat.detail', $daftar2->id));
    }

    public function test_riwayat_pendaftaran_is_ordered_from_newest_to_oldest(): void
    {
        $pasien = $this->createPasien();
        $dokterData = $this->createDokterWithJadwal();

        $daftar1 = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Keluhan Pertama Paling Lama',
            'no_antrian' => 1,
        ]);

        $daftar2 = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Keluhan Kedua Paling Baru',
            'no_antrian' => 2,
        ]);

        $response = $this->actingAs($pasien)->get(route('pasien.riwayat.index'));
        $response->assertStatus(200);

        $viewData = $response->viewData('riwayatPendaftaran');
        $this->assertEquals($daftar2->id, $viewData->first()->id);
        $this->assertEquals($daftar1->id, $viewData->last()->id);
    }

    public function test_pasien_only_sees_their_own_registrations(): void
    {
        $pasien1 = $this->createPasien('Pasien 1', 'pasien1@test.com');
        $pasien2 = $this->createPasien('Pasien 2', 'pasien2@test.com');
        $dokterData = $this->createDokterWithJadwal('Poli Bedah', 'dr. Anton Sp.B');

        DaftarPoli::create([
            'id_pasien' => $pasien1->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Keluhan Khusus Pasien 1',
            'no_antrian' => 1,
        ]);

        DaftarPoli::create([
            'id_pasien' => $pasien2->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Keluhan Khusus Pasien 2',
            'no_antrian' => 2,
        ]);

        $response1 = $this->actingAs($pasien1)->get(route('pasien.riwayat.index'));
        $response1->assertStatus(200);
        $this->assertCount(1, $response1->viewData('riwayatPendaftaran'));
        $this->assertEquals($pasien1->id, $response1->viewData('riwayatPendaftaran')->first()->id_pasien);
    }

    public function test_pasien_can_view_detail_examination_page(): void
    {
        $pasien = $this->createPasien();
        $dokterData = $this->createDokterWithJadwal('Poli Gigi', 'drg. Hendra');

        $daftar = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Gigi geraham ngilu saat mengunyah',
            'no_antrian' => 5,
        ]);

        $obat1 = Obat::create([
            'nama_obat' => 'Amoxicillin 500mg',
            'kemasan' => 'Strip 10 Kaplet',
            'harga' => 15000,
        ]);

        $obat2 = Obat::create([
            'nama_obat' => 'Asam Mefenamat 500mg',
            'kemasan' => 'Strip 10 Tablet',
            'harga' => 12000,
        ]);

        $periksa = Periksa::create([
            'id_daftar_poli' => $daftar->id,
            'tgl_periksa' => now(),
            'catatan' => 'Gigi berlubang pada molar 2 kanan bawah, telah dilakukan penambalan sementara.',
            'biaya_periksa' => 177000,
        ]);

        DetailPeriksa::create([
            'id_periksa' => $periksa->id,
            'id_obat' => $obat1->id,
        ]);

        DetailPeriksa::create([
            'id_periksa' => $periksa->id,
            'id_obat' => $obat2->id,
        ]);

        $response = $this->actingAs($pasien)->get(route('pasien.riwayat.detail', $daftar->id));

        $response->assertStatus(200);
        $response->assertSee('Detail Pemeriksaan Poli');
        $response->assertSee('Gigi berlubang pada molar 2 kanan bawah, telah dilakukan penambalan sementara.');
        $response->assertSee('Amoxicillin 500mg');
        $response->assertSee('Asam Mefenamat 500mg');
        $response->assertSee('Rp 177.000');
    }

    public function test_pasien_cannot_view_unexamined_detail(): void
    {
        $pasien = $this->createPasien();
        $dokterData = $this->createDokterWithJadwal();

        $daftar = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Belum diperiksa dokter',
            'no_antrian' => 3,
        ]);

        $response = $this->actingAs($pasien)->get(route('pasien.riwayat.detail', $daftar->id));
        $response->assertRedirect(route('pasien.riwayat.index'));
        $response->assertSessionHas('error');
    }

    public function test_pasien_cannot_view_another_pasiens_detail(): void
    {
        $pasien1 = $this->createPasien('Pasien 1', 'p1@test.com');
        $pasien2 = $this->createPasien('Pasien 2', 'p2@test.com');
        $dokterData = $this->createDokterWithJadwal();

        $daftarPasien2 = DaftarPoli::create([
            'id_pasien' => $pasien2->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Keluhan Pasien 2',
            'no_antrian' => 4,
        ]);

        Periksa::create([
            'id_daftar_poli' => $daftarPasien2->id,
            'tgl_periksa' => now(),
            'catatan' => 'Catatan Pasien 2',
            'biaya_periksa' => 150000,
        ]);

        // Pasien 1 mencoba mengakses detail milik Pasien 2 -> harus 404
        $response = $this->actingAs($pasien1)->get(route('pasien.riwayat.detail', $daftarPasien2->id));
        $response->assertStatus(404);
    }

    public function test_efficient_eager_loading_avoids_n_plus_one_queries(): void
    {
        $pasien = $this->createPasien();
        $dokterData1 = $this->createDokterWithJadwal('Poli Umum', 'dr. A');
        $dokterData2 = $this->createDokterWithJadwal('Poli THT', 'dr. B');

        // Buat 5 data pendaftaran
        for ($i = 1; $i <= 5; $i++) {
            $jadwal = ($i % 2 == 0) ? $dokterData1['jadwal'] : $dokterData2['jadwal'];
            $daftar = DaftarPoli::create([
                'id_pasien' => $pasien->id,
                'id_jadwal' => $jadwal->id,
                'keluhan' => 'Keluhan ' . $i,
                'no_antrian' => $i,
            ]);

            if ($i % 2 == 0) {
                Periksa::create([
                    'id_daftar_poli' => $daftar->id,
                    'tgl_periksa' => now(),
                    'catatan' => 'Catatan ' . $i,
                    'biaya_periksa' => 150000,
                ]);
            }
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($pasien)->get(route('pasien.riwayat.index'));
        $response->assertStatus(200);

        $queries = DB::getQueryLog();
        // Tanpa eager loading, 5 pendaftaran akan menghasilkan > 15 queries (N+1).
        // Dengan eager loading yang efisien, jumlah kueri untuk mengambil relasi DaftarPoli, Jadwal, Dokter, Poli, dan Periksa hanya ~5-6 kueri.
        $this->assertLessThanOrEqual(7, count($queries));
    }
}
