<?php

namespace Tests\Feature;

use App\Models\Poli;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_profile_page(): void
    {
        $response = $this->get(route('profile.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_update_password(): void
    {
        $response = $this->put(route('profile.password.update'), [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_all_roles_can_access_profile_page(): void
    {
        // 1. Admin
        $admin = User::create([
            'nama' => 'Admin Test',
            'email' => 'admin_test@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $responseAdmin = $this->actingAs($admin)->get(route('profile.index'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Ganti Password');
        $responseAdmin->assertSee('Profil & Pengaturan Akun', false);
        $responseAdmin->assertSee('Admin Test');

        // 2. Dokter
        $poli = Poli::create([
            'nama_poli' => 'Poli Mata',
            'keterangan' => 'Poli Spesialis Mata',
        ]);

        $dokter = User::create([
            'nama' => 'dr. Budi Santoso',
            'email' => 'dokter_budi@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'dokter',
            'id_poli' => $poli->id,
        ]);

        $responseDokter = $this->actingAs($dokter)->get(route('profile.index'));
        $responseDokter->assertStatus(200);
        $responseDokter->assertSee('Ganti Password');
        $responseDokter->assertSee('dr. Budi Santoso');
        $responseDokter->assertSee('Poli Mata');

        // 3. Pasien
        $pasien = User::create([
            'nama' => 'Siti Aminah',
            'email' => 'siti_pasien@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'pasien',
            'no_rm' => '202610-999',
        ]);

        $responsePasien = $this->actingAs($pasien)->get(route('profile.index'));
        $responsePasien->assertStatus(200);
        $responsePasien->assertSee('Ganti Password');
        $responsePasien->assertSee('Siti Aminah');
        $responsePasien->assertSee('202610-999');
    }

    public function test_password_change_requires_current_password(): void
    {
        $user = User::create([
            'nama' => 'Test User',
            'email' => 'testuser@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'pasien',
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => '',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors(['current_password']);
        $this->assertEquals(
            'Password lama wajib diisi.',
            session('errors')->first('current_password')
        );
    }

    public function test_password_change_fails_if_current_password_is_incorrect(): void
    {
        $user = User::create([
            'nama' => 'Test User',
            'email' => 'testuser@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'pasien',
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors(['current_password']);
        $this->assertEquals(
            'Password lama tidak cocok dengan password akun Anda.',
            session('errors')->first('current_password')
        );
    }

    public function test_password_change_fails_if_new_password_is_less_than_8_characters(): void
    {
        $user = User::create([
            'nama' => 'Test User',
            'email' => 'testuser@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'pasien',
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'password123',
            'password' => 'short77',
            'password_confirmation' => 'short77',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertEquals(
            'Password baru minimal 8 karakter.',
            session('errors')->first('password')
        );
    }

    public function test_password_change_fails_if_confirmation_does_not_match(): void
    {
        $user = User::create([
            'nama' => 'Test User',
            'email' => 'testuser@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'pasien',
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'mismatched123',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertEquals(
            'Konfirmasi password baru tidak cocok.',
            session('errors')->first('password')
        );
    }

    public function test_password_change_fails_if_new_password_is_same_as_old_password(): void
    {
        $user = User::create([
            'nama' => 'Test User',
            'email' => 'testuser@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'pasien',
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'password123',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertEquals(
            'Password baru tidak boleh sama dengan password lama.',
            session('errors')->first('password')
        );
    }

    public function test_password_change_succeeds_and_is_stored_as_hash(): void
    {
        $user = User::create([
            'nama' => 'Test User',
            'email' => 'testuser@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'password123',
            'password' => 'newsecretpass2026',
            'password_confirmation' => 'newsecretpass2026',
        ]);

        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHas('success', 'Password berhasil diganti!');

        // Refresh user dari database
        $user->refresh();

        // Password harus tersimpan dalam bentuk hash, bukan teks biasa
        $this->assertNotEquals('newsecretpass2026', $user->password);
        $this->assertTrue(Hash::check('newsecretpass2026', $user->password));

        // Pastikan login dengan password baru berhasil
        $this->assertTrue(Auth::attempt([
            'email' => 'testuser@gmail.com',
            'password' => 'newsecretpass2026',
        ]));

        // Pastikan login dengan password lama gagal
        $this->assertFalse(Auth::attempt([
            'email' => 'testuser@gmail.com',
            'password' => 'password123',
        ]));
    }

    public function test_user_only_modifies_their_own_password(): void
    {
        $user1 = User::create([
            'nama' => 'User One',
            'email' => 'user1@gmail.com',
            'password' => Hash::make('originalpass1'),
            'role' => 'dokter',
        ]);

        $user2 = User::create([
            'nama' => 'User Two',
            'email' => 'user2@gmail.com',
            'password' => Hash::make('originalpass2'),
            'role' => 'pasien',
        ]);

        // User 1 login dan ubah passwordnya
        $response = $this->actingAs($user1)->put(route('profile.password.update'), [
            'current_password' => 'originalpass1',
            'password' => 'brandnewpass1',
            'password_confirmation' => 'brandnewpass1',
            'id' => $user2->id, // Mencoba menyelipkan ID user 2
        ]);

        $response->assertRedirect(route('profile.index'));

        // Refresh kedua user
        $user1->refresh();
        $user2->refresh();

        // Password User 1 berubah
        $this->assertTrue(Hash::check('brandnewpass1', $user1->password));

        // Password User 2 tetap aman dan tidak berubah
        $this->assertTrue(Hash::check('originalpass2', $user2->password));
        $this->assertFalse(Hash::check('brandnewpass1', $user2->password));
    }
}
