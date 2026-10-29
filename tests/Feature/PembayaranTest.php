<?php

namespace Tests\Feature;

use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Periksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PembayaranTest extends TestCase
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
            'alamat' => 'Jl. Kenanga No. 1',
        ]);
    }

    private function createAdmin(): User
    {
        return User::create([
            'nama' => 'Admin Utama',
            'email' => 'admin_pembayaran@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
    }

    private function createDokterWithJadwal(): array
    {
        $poli = Poli::create([
            'nama_poli' => 'Poli Umum',
            'keterangan' => 'Poli Umum Test',
        ]);

        $dokter = User::create([
            'nama' => 'dr. Santoso',
            'email' => 'santoso@test.com',
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

    public function test_guest_cannot_access_pembayaran(): void
    {
        $response = $this->get(route('pasien.pembayaran.index'));
        $response->assertRedirect(route('login'));

        $responseAdmin = $this->get(route('admin.pembayaran.index'));
        $responseAdmin->assertRedirect(route('login'));
    }

    public function test_pasien_can_view_their_bills(): void
    {
        $pasien = $this->createPasien();
        $dokterData = $this->createDokterWithJadwal();

        $daftar = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Demam tinggi',
            'no_antrian' => 1,
        ]);

        $periksa = Periksa::create([
            'id_daftar_poli' => $daftar->id,
            'tgl_periksa' => now(),
            'catatan' => 'Istirahat dan minum obat teratur',
            'biaya_periksa' => 180000,
            'status_pembayaran' => 'belum_bayar',
        ]);

        $response = $this->actingAs($pasien)->get(route('pasien.pembayaran.index'));
        $response->assertStatus(200);
        $response->assertSee('Pembayaran &amp; Tagihan', false);
        $response->assertSee('Rp 180.000');
        $response->assertSee('Belum Bayar');
        $response->assertSee('Upload Bukti');
    }

    public function test_pasien_can_upload_payment_proof(): void
    {
        Storage::fake('public');

        $pasien = $this->createPasien();
        $dokterData = $this->createDokterWithJadwal();

        $daftar = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Sakit perut',
            'no_antrian' => 2,
        ]);

        $periksa = Periksa::create([
            'id_daftar_poli' => $daftar->id,
            'tgl_periksa' => now(),
            'catatan' => 'Konsumsi obat maag',
            'biaya_periksa' => 165000,
            'status_pembayaran' => 'belum_bayar',
        ]);

        // Tiny valid JPEG binary (1x1 pixel) agar valid tanpa butuh ekstensi PHP GD
        $jpegContent = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=');
        $file = UploadedFile::fake()->createWithContent('bukti_transfer.jpg', $jpegContent);

        $response = $this->actingAs($pasien)->post(route('pasien.pembayaran.upload', $periksa->id), [
            'bukti_pembayaran' => $file,
            'catatan_pembayaran' => 'Transfer via Mandiri atas nama Budi',
        ]);

        $response->assertRedirect(route('pasien.pembayaran.index'));
        $response->assertSessionHas('success');

        $periksa->refresh();
        $this->assertEquals('menunggu_verifikasi', $periksa->status_pembayaran);
        $this->assertNotNull($periksa->bukti_pembayaran);
        $this->assertNotNull($periksa->tgl_bayar);
        $this->assertEquals('Transfer via Mandiri atas nama Budi', $periksa->catatan_pembayaran);

        Storage::disk('public')->assertExists($periksa->bukti_pembayaran);
    }

    public function test_upload_proof_requires_valid_image(): void
    {
        Storage::fake('public');

        $pasien = $this->createPasien();
        $dokterData = $this->createDokterWithJadwal();

        $daftar = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Keluhan',
            'no_antrian' => 1,
        ]);

        $periksa = Periksa::create([
            'id_daftar_poli' => $daftar->id,
            'tgl_periksa' => now(),
            'catatan' => 'Catatan',
            'biaya_periksa' => 150000,
        ]);

        // 1. File kosong
        $responseEmpty = $this->actingAs($pasien)->post(route('pasien.pembayaran.upload', $periksa->id), [
            'bukti_pembayaran' => '',
        ]);
        $responseEmpty->assertSessionHasErrors(['bukti_pembayaran']);

        // 2. File non-image (misal txt)
        $txtFile = UploadedFile::fake()->create('document.txt', 100);
        $responseTxt = $this->actingAs($pasien)->post(route('pasien.pembayaran.upload', $periksa->id), [
            'bukti_pembayaran' => $txtFile,
        ]);
        $responseTxt->assertSessionHasErrors(['bukti_pembayaran']);
    }

    public function test_admin_can_view_pending_payments(): void
    {
        $admin = $this->createAdmin();
        $pasien = $this->createPasien('Pasien Verifikasi', 'verif@test.com');
        $dokterData = $this->createDokterWithJadwal();

        $daftar = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Demam',
            'no_antrian' => 3,
        ]);

        Periksa::create([
            'id_daftar_poli' => $daftar->id,
            'tgl_periksa' => now(),
            'catatan' => 'Resep antibiotik',
            'biaya_periksa' => 195000,
            'status_pembayaran' => 'menunggu_verifikasi',
            'bukti_pembayaran' => 'bukti_pembayaran/dummy.jpg',
            'tgl_bayar' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.pembayaran.index', ['status' => 'menunggu_verifikasi']));

        $response->assertStatus(200);
        $response->assertSee('Verifikasi Pembayaran Pasien');
        $response->assertSee('Pasien Verifikasi');
        $response->assertSee('Rp 195.000');
        $response->assertSee('Konfirmasi Lunas');
    }

    public function test_admin_can_confirm_payment_to_lunas(): void
    {
        $admin = $this->createAdmin();
        $pasien = $this->createPasien();
        $dokterData = $this->createDokterWithJadwal();

        $daftar = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Keluhan',
            'no_antrian' => 1,
        ]);

        $periksa = Periksa::create([
            'id_daftar_poli' => $daftar->id,
            'tgl_periksa' => now(),
            'catatan' => 'Catatan',
            'biaya_periksa' => 150000,
            'status_pembayaran' => 'menunggu_verifikasi',
            'bukti_pembayaran' => 'bukti_pembayaran/dummy.jpg',
            'tgl_bayar' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pembayaran.konfirmasi', $periksa->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $periksa->refresh();
        $this->assertEquals('lunas', $periksa->status_pembayaran);
        $this->assertNotNull($periksa->tgl_konfirmasi);
    }

    public function test_non_admin_cannot_confirm_payment(): void
    {
        $pasien = $this->createPasien();
        $dokterData = $this->createDokterWithJadwal();

        $daftar = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Keluhan',
            'no_antrian' => 1,
        ]);

        $periksa = Periksa::create([
            'id_daftar_poli' => $daftar->id,
            'tgl_periksa' => now(),
            'biaya_periksa' => 150000,
            'status_pembayaran' => 'menunggu_verifikasi',
        ]);

        $response = $this->actingAs($pasien)->post(route('admin.pembayaran.konfirmasi', $periksa->id));
        $response->assertStatus(403);
    }
}
