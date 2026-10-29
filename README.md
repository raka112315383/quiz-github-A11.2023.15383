# Sistem Informasi Poliklinik (Bengkel Koding)

> **Tugas / Quiz Pemrograman Web - Bengkel Koding**  
> **NIM**: A11.2023.15383  
> **Repository**: [quiz-github-A11.2023.15383](https://github.com/raka112315383/quiz-github-A11.2023.15383)

---

## 📌 Ringkasan Pembaruan & Fitur-Fitur Baru

Berikut adalah daftar fitur baru dan pembaruan sistem yang telah diimplementasikan pada aplikasi Poliklinik ini:

| No | Fitur | Deskripsi & Komponen Utama | Aktor / Role |
|---|---|---|---|
| 1 | **Profil & Ganti Password** | Halaman kelola profil pengguna dan form ubah kata sandi dengan validasi password lama (`UpdatePasswordRequest`). | Semua Role (Admin, Dokter, Pasien) |
| 2 | **Sistem Pembayaran Pasien & Kasir** | Rincian total tagihan (biaya jasa + total obat), upload bukti transfer/QRIS, serta verifikasi pembayaran oleh kasir/admin. | Pasien & Admin |
| 3 | **Notifikasi Sistem & Real-time** | Dropdown lonceng notifikasi di header, badge count belum dibaca, halaman daftar notifikasi, serta event broadcast real-time (`AntrianUpdatedEvent`). | Admin & Pasien |
| 4 | **Antrean Pasien & Riwayat Pendaftaran** | Dashboard pemantauan nomor antrean langsung, estimasi antrean saat ini, dan riwayat pendaftaran pemeriksaan pasien. | Pasien |
| 5 | **Pengelolaan Stok Obat & Export Data** | Penambahan stok pada data obat, pengurangan otomatis saat pemeriksaan, serta fitur export laporan ke Excel (`ExcelExportService`). | Admin & Dokter |
| 6 | **Automated Feature Testing** | Rangkaian unit & feature tests menggunakan PHPUnit / Pest untuk menjamin kehandalan seluruh fitur baru. | Developer / QA |

---

## 🚀 Rincian Implementasi Fitur

### 1. 👤 Profil dan Ganti Password
- **Controller**: `app/Http/Controllers/ProfileController.php`
- **Request Validation**: `app/Http/Requests/UpdatePasswordRequest.php`
- **Tampilan (View)**: `resources/views/profile/index.blade.php`
- **Fitur**: Memperbarui informasi akun (nama, email, no HP, alamat) dan fitur ubah password terenkripsi Hash (Bcrypt).

### 2. 💳 Pembayaran & Tagihan Periksa
- **Migration**: `database/migrations/2026_10_27_000001_add_pembayaran_to_periksa_table.php`
- **Controllers**:
  - `app/Http/Controllers/Pasien/PembayaranController.php` (Unggah bukti bayar, lihat tagihan)
  - `app/Http/Controllers/Admin/PembayaranController.php` (Verifikasi & konfirmasi pembayaran)
- **Tampilan (View)**:
  - `resources/views/pasien/pembayaran/index.blade.php`
  - `resources/views/admin/pembayaran/index.blade.php`
- **Notifications**:
  - `app/Notifications/BuktiPembayaranDiunggahNotification.php`
  - `app/Notifications/PembayaranDiverifikasiNotification.php`

### 3. 🔔 Notifikasi Sistem (Database & Broadcast)
- **Migration**: `database/migrations/2026_10_27_101418_create_notifications_table.php`
- **Controller**: `app/Http/Controllers/NotificationController.php`
- **Tampilan (View)**: `resources/views/notifikasi/index.blade.php` & partials header `header.blade.php`
- **Broadcasting & Websocket**:
  - `config/broadcasting.php`, `config/reverb.php`
  - `routes/channels.php`, `resources/js/echo.js`
  - `app/Events/AntrianUpdatedEvent.php`

### 4. 🏥 Antrean & Riwayat Pendaftaran Pasien
- **Controllers**:
  - `app/Http/Controllers/Pasien/DashboardController.php`
  - `app/Http/Controllers/Pasien/RiwayatPendaftaranController.php`
- **Tampilan (View)**:
  - `resources/views/pasien/dashboard.blade.php` (Card antrean aktif, status pendaftaran)
  - `resources/views/pasien/riwayat/index.blade.php` & `detail.blade.php`

### 5. 💊 Pengelolaan Stok Obat & Export Excel
- **Migration**: `database/migrations/2026_10_27_000002_add_stok_to_obat_table.php`
- **Service**: `app/Services/ExcelExportService.php`
- **Controllers**:
  - `app/Http/Controllers/Admin/ObatController.php`
  - `app/Http/Controllers/Dokter/PeriksaPasienController.php`
- **Tampilan (View)**: `resources/views/admin/obat/`

### 6. 🧪 Automated Testing (Feature Tests)
File pengujian otomatis tersedia di folder `tests/Feature/`:
- `tests/Feature/PasswordChangeTest.php` — Pengujian ubah password & validasi
- `tests/Feature/PembayaranTest.php` — Pengujian alur pembayaran dan verifikasi kasir
- `tests/Feature/NotifikasiTest.php` — Pengujian pembuatan dan status baca notifikasi
- `tests/Feature/DashboardPasienAntrianTest.php` — Pengujian tampilan antrean pasien
- `tests/Feature/RiwayatPendaftaranPasienTest.php` — Pengujian daftar riwayat pasien
- `tests/Feature/ExportExcelTest.php` — Pengujian ekspor data ke Excel

---

## 🛠️ Panduan Instalasi & Menjalankan Proyek

1. **Clone Repository**:
   ```bash
   git clone https://github.com/raka112315383/quiz-github-A11.2023.15383.git
   cd poliklinik-app
   ```

2. **Install Dependensi PHP & JavaScript**:
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Sesuaikan konfigurasi database `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di `.env`.*

4. **Jalankan Migrasi & Seeder**:
   ```bash
   php artisan migrate --seed
   ```

5. **Jalankan Server Lokal**:
   ```bash
   npm run dev
   php artisan serve
   ```

6. **Menjalankan Pengujian Otomatis (Tests)**:
   ```bash
   php artisan test
   ```
