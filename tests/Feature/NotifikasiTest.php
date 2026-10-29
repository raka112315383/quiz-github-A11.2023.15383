<?php

namespace Tests\Feature;

use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Periksa;
use App\Models\Poli;
use App\Models\User;
use App\Notifications\BuktiPembayaranDiunggahNotification;
use App\Notifications\PembayaranDiverifikasiNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotifikasiTest extends TestCase
{
    use RefreshDatabase;

    private function createPasien(string $nama = 'Budi Pasien', string $email = 'budi_notif@test.com'): User
    {
        return User::create([
            'nama' => $nama,
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => 'pasien',
            'no_rm' => '202610-001',
            'no_ktp' => '3301010101010001',
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Anggrek No. 5',
        ]);
    }

    private function createDokter(string $nama = 'dr. Maya Sp.A', string $email = 'maya@test.com'): array
    {
        $poli = Poli::create([
            'nama_poli' => 'Poli Anak',
            'keterangan' => 'Poli Spesialis Anak',
        ]);

        $dokter = User::create([
            'nama' => $nama,
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => 'dokter',
            'id_poli' => $poli->id,
        ]);

        $jadwal = JadwalPeriksa::create([
            'id_dokter' => $dokter->id,
            'hari' => 'Selasa',
            'jam_mulai' => '09:00',
            'jam_selesai' => '13:00',
        ]);

        return compact('poli', 'dokter', 'jadwal');
    }

    private function createAdmin(): User
    {
        return User::create([
            'nama' => 'Admin Notif',
            'email' => 'admin_notif@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
    }

    public function test_guest_cannot_access_notifications(): void
    {
        $response = $this->get(route('notifikasi.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_view_their_own_notifications(): void
    {
        $pasien1 = $this->createPasien('Pasien Satu', 'p1@test.com');
        $pasien2 = $this->createPasien('Pasien Dua', 'p2@test.com');

        $dokterData = $this->createDokter();

        $daftar = DaftarPoli::create([
            'id_pasien' => $pasien1->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Demam',
            'no_antrian' => 1,
        ]);

        $periksa = Periksa::create([
            'id_daftar_poli' => $daftar->id,
            'tgl_periksa' => now(),
            'catatan' => 'Catatan',
            'biaya_periksa' => 150000,
        ]);

        // Kirim notifikasi ke Pasien 1
        $pasien1->notify(new PembayaranDiverifikasiNotification($periksa));

        // Pasien 1 mengakses halaman notifikasi
        $response1 = $this->actingAs($pasien1)->get(route('notifikasi.index'));
        $response1->assertStatus(200);
        $response1->assertSee('Pembayaran Berhasil Diverifikasi');
        $response1->assertSee('Rp 150.000');

        // Pasien 2 mengakses halaman notifikasi -> tidak boleh melihat notifikasi milik Pasien 1
        $response2 = $this->actingAs($pasien2)->get(route('notifikasi.index'));
        $response2->assertStatus(200);
        $response2->assertDontSee('Pembayaran Berhasil Diverifikasi');
        $response2->assertSee('Belum Ada Notifikasi');
    }

    public function test_unread_count_badge_is_displayed_on_navbar(): void
    {
        $pasien = $this->createPasien();
        $dokterData = $this->createDokter();

        $daftar = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Keluhan',
            'no_antrian' => 1,
        ]);

        $periksa = Periksa::create([
            'id_daftar_poli' => $daftar->id,
            'tgl_periksa' => now(),
            'biaya_periksa' => 200000,
        ]);

        $pasien->notify(new PembayaranDiverifikasiNotification($periksa));

        $response = $this->actingAs($pasien)->get(route('pasien.dashboard'));
        $response->assertStatus(200);
        // Cek lonceng dan badge unread
        $response->assertSee('fa-bell');
        $response->assertSee('1 Baru');
    }

    public function test_user_can_mark_single_notification_as_read(): void
    {
        $pasien = $this->createPasien();
        $dokterData = $this->createDokter();

        $daftar = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Keluhan',
            'no_antrian' => 1,
        ]);

        $periksa = Periksa::create([
            'id_daftar_poli' => $daftar->id,
            'tgl_periksa' => now(),
            'biaya_periksa' => 170000,
        ]);

        $pasien->notify(new PembayaranDiverifikasiNotification($periksa));
        $notification = $pasien->unreadNotifications()->first();

        $this->assertNotNull($notification);
        $this->assertNull($notification->read_at);

        $response = $this->actingAs($pasien)->post(route('notifikasi.markAsRead', $notification->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(0, $pasien->unreadNotifications()->count());
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_other_users_notification_as_read(): void
    {
        $pasien1 = $this->createPasien('Pasien 1', 'pasien1@test.com');
        $pasien2 = $this->createPasien('Pasien 2', 'pasien2@test.com');
        $dokterData = $this->createDokter();

        $daftar = DaftarPoli::create([
            'id_pasien' => $pasien1->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Keluhan',
            'no_antrian' => 1,
        ]);

        $periksa = Periksa::create([
            'id_daftar_poli' => $daftar->id,
            'tgl_periksa' => now(),
            'biaya_periksa' => 150000,
        ]);

        $pasien1->notify(new PembayaranDiverifikasiNotification($periksa));
        $notifPasien1 = $pasien1->unreadNotifications()->first();

        // Pasien 2 mencoba menandai notifikasi milik Pasien 1 -> harus 404
        $response = $this->actingAs($pasien2)->post(route('notifikasi.markAsRead', $notifPasien1->id));
        $response->assertStatus(404);

        $this->assertNull($notifPasien1->fresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $pasien = $this->createPasien();
        $dokterData = $this->createDokter();

        $daftar = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Keluhan',
            'no_antrian' => 1,
        ]);

        $periksa1 = Periksa::create([
            'id_daftar_poli' => $daftar->id,
            'tgl_periksa' => now(),
            'biaya_periksa' => 150000,
        ]);

        $periksa2 = Periksa::create([
            'id_daftar_poli' => $daftar->id,
            'tgl_periksa' => now(),
            'biaya_periksa' => 160000,
        ]);

        $pasien->notify(new PembayaranDiverifikasiNotification($periksa1));
        $pasien->notify(new PembayaranDiverifikasiNotification($periksa2));

        $this->assertEquals(2, $pasien->unreadNotifications()->count());

        $response = $this->actingAs($pasien)->post(route('notifikasi.markAllAsRead'));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(0, $pasien->unreadNotifications()->count());
    }

    public function test_empty_state_is_displayed_when_no_notifications_exist(): void
    {
        $pasien = $this->createPasien();

        $response = $this->actingAs($pasien)->get(route('notifikasi.index'));
        $response->assertStatus(200);
        $response->assertSee('Belum Ada Notifikasi');
    }

    public function test_doctor_receives_notification_when_patient_uploads_payment_proof(): void
    {
        Storage::fake('public');

        $pasien = $this->createPasien();
        $dokterData = $this->createDokter('dr. Maya Sp.A', 'dokter_maya@test.com');
        $dokter = $dokterData['dokter'];

        $daftar = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Sakit telinga',
            'no_antrian' => 1,
        ]);

        $periksa = Periksa::create([
            'id_daftar_poli' => $daftar->id,
            'tgl_periksa' => now(),
            'biaya_periksa' => 175000,
            'status_pembayaran' => 'belum_bayar',
        ]);

        $this->assertEquals(0, $dokter->unreadNotifications()->count());

        $jpegContent = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=');
        $file = UploadedFile::fake()->createWithContent('bukti_transfer.jpg', $jpegContent);

        $response = $this->actingAs($pasien)->post(route('pasien.pembayaran.upload', $periksa->id), [
            'bukti_pembayaran' => $file,
        ]);

        $response->assertRedirect(route('pasien.pembayaran.index'));

        // Dokter pemeriksa harus menerima 1 notifikasi
        $dokter->refresh();
        $this->assertEquals(1, $dokter->unreadNotifications()->count());
        $notif = $dokter->unreadNotifications()->first();
        $this->assertEquals(BuktiPembayaranDiunggahNotification::class, $notif->type);
        $this->assertStringContainsString('Pasien Mengunggah Bukti Pembayaran', $notif->data['title']);

        // Upload ulang tidak boleh membuat notifikasi ganda jika belum dibaca
        $file2 = UploadedFile::fake()->createWithContent('bukti_transfer2.jpg', $jpegContent);
        $this->actingAs($pasien)->post(route('pasien.pembayaran.upload', $periksa->id), [
            'bukti_pembayaran' => $file2,
        ]);

        $this->assertEquals(1, $dokter->unreadNotifications()->count());
    }

    public function test_patient_receives_notification_when_payment_is_confirmed_by_admin(): void
    {
        $admin = $this->createAdmin();
        $pasien = $this->createPasien('Pasien Target', 'target@test.com');
        $dokterData = $this->createDokter();

        $daftar = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $dokterData['jadwal']->id,
            'keluhan' => 'Keluhan',
            'no_antrian' => 1,
        ]);

        $periksa = Periksa::create([
            'id_daftar_poli' => $daftar->id,
            'tgl_periksa' => now(),
            'biaya_periksa' => 180000,
            'status_pembayaran' => 'menunggu_verifikasi',
            'bukti_pembayaran' => 'bukti_pembayaran/dummy.jpg',
            'tgl_bayar' => now(),
        ]);

        $this->assertEquals(0, $pasien->unreadNotifications()->count());

        $response = $this->actingAs($admin)->post(route('admin.pembayaran.konfirmasi', $periksa->id));
        $response->assertRedirect();

        // Pasien harus menerima notifikasi pembayaran diverifikasi
        $pasien->refresh();
        $this->assertEquals(1, $pasien->unreadNotifications()->count());
        $notif = $pasien->unreadNotifications()->first();
        $this->assertEquals(PembayaranDiverifikasiNotification::class, $notif->type);
        $this->assertStringContainsString('Pembayaran Berhasil Diverifikasi', $notif->data['title']);

        // Konfirmasi ulang tidak boleh membuat notifikasi ganda
        $this->actingAs($admin)->post(route('admin.pembayaran.konfirmasi', $periksa->id));
        $this->assertEquals(1, $pasien->notifications()->count());
    }
}
