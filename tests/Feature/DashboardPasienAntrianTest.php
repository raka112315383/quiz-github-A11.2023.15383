<?php

namespace Tests\Feature;

use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Periksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPasienAntrianTest extends TestCase
{
    use RefreshDatabase;

    protected User $pasien;
    protected User $dokter1;
    protected User $dokter2;
    protected Poli $poliGigi;
    protected Poli $poliUmum;
    protected JadwalPeriksa $jadwalGigi;
    protected JadwalPeriksa $jadwalUmum;

    protected function setUp(): void
    {
        parent::setUp();

        $this->poliGigi = Poli::create([
            'nama_poli' => 'Poli Gigi',
            'deskripsi' => 'Pelayanan Gigi dan Mulut',
        ]);

        $this->poliUmum = Poli::create([
            'nama_poli' => 'Poli Umum',
            'deskripsi' => 'Pelayanan Medis Umum',
        ]);

        $this->dokter1 = User::create([
            'nama' => 'Dr. Dokter Gigi',
            'email' => 'dokter_gigi@poli.test',
            'password' => bcrypt('password'),
            'role' => 'dokter',
            'id_poli' => $this->poliGigi->id,
        ]);

        $this->dokter2 = User::create([
            'nama' => 'Dr. Dokter Umum',
            'email' => 'dokter_umum@poli.test',
            'password' => bcrypt('password'),
            'role' => 'dokter',
            'id_poli' => $this->poliUmum->id,
        ]);

        $this->jadwalGigi = JadwalPeriksa::create([
            'id_dokter' => $this->dokter1->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:00',
        ]);

        $this->jadwalUmum = JadwalPeriksa::create([
            'id_dokter' => $this->dokter2->id,
            'hari' => 'Selasa',
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
        ]);

        $this->pasien = User::create([
            'nama' => 'Pasien Budi',
            'email' => 'budi@pasien.test',
            'password' => bcrypt('password'),
            'role' => 'pasien',
            'no_rm' => '202610-099',
        ]);
    }

    public function test_pasien_without_active_queue_sees_empty_state(): void
    {
        $response = $this->actingAs($this->pasien)->get(route('pasien.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Anda Belum Memiliki Antrian Aktif');
        $response->assertSee('Poli Gigi');
        $response->assertSee('Dr. Dokter Gigi');
    }

    public function test_pasien_with_active_queue_sees_banner_with_full_info(): void
    {
        // Pasien mendaftar di jadwal Gigi dan mendapat antrian #5
        // Buat 4 antrian sebelumnya yang sudah diperiksa
        for ($i = 1; $i <= 4; $i++) {
            $otherPasien = User::create([
                'nama' => "Pasien $i",
                'email' => "pasien$i@test.com",
                'password' => bcrypt('password'),
                'role' => 'pasien',
            ]);

            $daftar = DaftarPoli::create([
                'id_pasien' => $otherPasien->id,
                'id_jadwal' => $this->jadwalGigi->id,
                'keluhan' => 'Sakit gigi',
                'no_antrian' => $i,
            ]);

            Periksa::create([
                'id_daftar_poli' => $daftar->id,
                'tgl_periksa' => now(),
                'catatan' => 'Selesai periksa',
                'biaya_periksa' => 150000,
            ]);
        }

        // Pasien Budi antrian #5 (belum diperiksa)
        DaftarPoli::create([
            'id_pasien' => $this->pasien->id,
            'id_jadwal' => $this->jadwalGigi->id,
            'keluhan' => 'Tambal gigi',
            'no_antrian' => 5,
        ]);

        $response = $this->actingAs($this->pasien)->get(route('pasien.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('ANTRIAN AKTIF ANDA');
        $response->assertSee('Poli Gigi');
        $response->assertSee('Dr. Dokter Gigi');
        $response->assertSee('Senin (08:00 - 09:00)');
        $response->assertSee('Nomor Anda');
        $response->assertSee('5');
        $response->assertSee('SEDANG DILAYANI');
        $response->assertSee('Live Update');
    }

    public function test_pasien_cannot_register_to_two_polis_simultaneously(): void
    {
        // Pasien Budi mendaftar di Poli Gigi
        DaftarPoli::create([
            'id_pasien' => $this->pasien->id,
            'id_jadwal' => $this->jadwalGigi->id,
            'keluhan' => 'Keluhan 1',
            'no_antrian' => 1,
        ]);

        // Coba mendaftar lagi ke Poli Umum saat antrian Gigi masih aktif
        $response = $this->actingAs($this->pasien)->post(route('pasien.daftar.submit'), [
            'id_pasien' => $this->pasien->id,
            'id_poli' => $this->poliUmum->id,
            'id_jadwal' => $this->jadwalUmum->id,
            'keluhan' => 'Mau periksa umum juga',
        ]);

        // Harus gagal dan diredirect dengan pesan error
        $response->assertSessionHas('type', 'error');
        $response->assertSessionHas('message');

        // Pastikan pendaftaran kedua tidak tersimpan di database
        $this->assertEquals(1, DaftarPoli::where('id_pasien', $this->pasien->id)->count());
    }

    public function test_pasien_with_active_queue_cannot_see_registration_form_in_daftar_page(): void
    {
        DaftarPoli::create([
            'id_pasien' => $this->pasien->id,
            'id_jadwal' => $this->jadwalGigi->id,
            'keluhan' => 'Sakit gigi',
            'no_antrian' => 1,
        ]);

        $response = $this->actingAs($this->pasien)->get(route('pasien.daftar'));

        $response->assertStatus(200);
        $response->assertSee('Pendaftaran Belum Dapat Dilakukan');
        $response->assertSee('Satu pasien tidak dapat mendaftar lebih dari satu kali ketika belum selesai');
        $response->assertDontSee('Pilih Jadwal Periksa');
    }

    public function test_pasien_can_register_again_after_examination_is_completed(): void
    {
        // Pasien mendaftar di Poli Gigi
        $daftarGigi = DaftarPoli::create([
            'id_pasien' => $this->pasien->id,
            'id_jadwal' => $this->jadwalGigi->id,
            'keluhan' => 'Keluhan Gigi',
            'no_antrian' => 1,
        ]);

        // Dokter memeriksa pasien (Pemeriksaan selesai)
        Periksa::create([
            'id_daftar_poli' => $daftarGigi->id,
            'tgl_periksa' => now(),
            'catatan' => 'Pemeriksaan gigi selesai',
            'biaya_periksa' => 170000,
        ]);

        // Pasien sekarang diperbolehkan mendaftar kembali
        $response = $this->actingAs($this->pasien)->post(route('pasien.daftar.submit'), [
            'id_pasien' => $this->pasien->id,
            'id_poli' => $this->poliUmum->id,
            'id_jadwal' => $this->jadwalUmum->id,
            'keluhan' => 'Flu dan batuk',
        ]);

        $response->assertRedirect(route('pasien.dashboard'));
        $this->assertEquals(2, DaftarPoli::where('id_pasien', $this->pasien->id)->count());
    }

    public function test_live_antrian_api_returns_correct_realtime_data(): void
    {
        // Antrian #1 untuk pasien lain
        $pasienA = User::create([
            'nama' => 'Pasien A',
            'email' => 'pasien_a@test.com',
            'password' => bcrypt('password'),
            'role' => 'pasien',
        ]);

        $daftarA = DaftarPoli::create([
            'id_pasien' => $pasienA->id,
            'id_jadwal' => $this->jadwalGigi->id,
            'keluhan' => 'Gigi berlubang',
            'no_antrian' => 1,
        ]);

        // Antrian #2 untuk Pasien Budi
        DaftarPoli::create([
            'id_pasien' => $this->pasien->id,
            'id_jadwal' => $this->jadwalGigi->id,
            'keluhan' => 'Pembersihan karang gigi',
            'no_antrian' => 2,
        ]);

        // Nomor yang sedang dilayani saat ini adalah #1
        $response = $this->actingAs($this->pasien)->getJson(route('pasien.antrian.live'));
        $response->assertStatus(200);
        $response->assertJson([
            'has_active' => true,
            'antrian_aktif' => [
                'poli' => 'Poli Gigi',
                'no_antrian' => 2,
                'sedang_dilayani' => 1,
                'is_your_turn' => false,
            ]
        ]);

        // Dokter menyelesaikan antrian #1
        Periksa::create([
            'id_daftar_poli' => $daftarA->id,
            'tgl_periksa' => now(),
            'catatan' => 'Selesai ditambal',
            'biaya_periksa' => 150000,
        ]);

        // Realtime sync: Nomor yang sedang dilayani otomatis naik ke #2 (giliran Pasien Budi)
        $response2 = $this->actingAs($this->pasien)->getJson(route('pasien.antrian.live'));
        $response2->assertStatus(200);
        $response2->assertJson([
            'has_active' => true,
            'antrian_aktif' => [
                'poli' => 'Poli Gigi',
                'no_antrian' => 2,
                'sedang_dilayani' => 2,
                'is_your_turn' => true,
            ]
        ]);
    }

    public function test_dashboard_displays_antrian_sedang_dipanggil_di_setiap_poli(): void
    {
        DaftarPoli::create([
            'id_pasien' => $this->pasien->id,
            'id_jadwal' => $this->jadwalGigi->id,
            'keluhan' => 'Gigi',
            'no_antrian' => 7,
        ]);

        $response = $this->actingAs($this->pasien)->get(route('pasien.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Nomor Antrian Sedang Dipanggil di Setiap Poli');
        $response->assertSee('Poli Gigi');
        $response->assertSee('Poli Umum');
    }

    public function test_dokter_can_panggil_pasien_and_event_is_broadcast_to_reverb(): void
    {
        \Illuminate\Support\Facades\Event::fake();

        $daftar = DaftarPoli::create([
            'id_pasien' => $this->pasien->id,
            'id_jadwal' => $this->jadwalGigi->id,
            'keluhan' => 'Sakit gigi geraham',
            'no_antrian' => 3,
        ]);

        $response = $this->actingAs($this->dokter1)->postJson(route('dokter.panggil', $daftar->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'no_antrian' => 3,
        ]);

        \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\AntrianUpdatedEvent::class, function ($event) {
            return $event->noAntrian === 3 &&
                   $event->idJadwal === $this->jadwalGigi->id &&
                   $event->aksi === 'dipanggil';
        });
    }

    public function test_antrian_event_broadcast_channel_and_reverb_contract(): void
    {
        $event = new \App\Events\AntrianUpdatedEvent(
            $this->poliGigi->id,
            $this->poliGigi->nama_poli,
            $this->jadwalGigi->id,
            5,
            'Pasien Budi',
            'Dr. Dokter Gigi',
            'dipanggil',
            $this->pasien->id
        );

        $channels = $event->broadcastOn();
        $this->assertCount(1, $channels);
        $this->assertEquals('antrian', $channels[0]->name);
        $this->assertEquals('AntrianUpdated', $event->broadcastAs());
        $this->assertInstanceOf(\Illuminate\Contracts\Broadcasting\ShouldBroadcastNow::class, $event);
    }
}
