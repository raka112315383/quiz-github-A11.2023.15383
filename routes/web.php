<?php

use App\Http\Controllers\Admin\AdminPoliController;
use App\Http\Controllers\Admin\DokterController;
use App\Http\Controllers\Admin\ObatController;
use App\Http\Controllers\Admin\PasienController;
use App\Http\Controllers\Admin\PembayaranController as AdminPembayaranController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dokter\JadwalPeriksaController;
use App\Http\Controllers\Dokter\PeriksaPasienController;
use App\Http\Controllers\Dokter\RiwayatPasienController;
use App\Http\Controllers\Pasien\DashboardController as PasienDashboardController;
use App\Http\Controllers\Pasien\PembayaranController as PasienPembayaranController;
use App\Http\Controllers\Pasien\PoliController as PasienPoliController;
use App\Http\Controllers\Pasien\RiwayatPendaftaranController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Shared Authenticated Routes (Dapat diakses oleh semua role: Admin, Dokter, Pasien)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Notifikasi
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifikasi.markAllAsRead');
    Route::post('/notifikasi/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifikasi.markAsRead');
});



// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        $totalDokter = \App\Models\User::where('role', 'dokter')->count();
        $totalPasien = \App\Models\User::where('role', 'pasien')->count();
        $totalPoli = \App\Models\Poli::count();
        $totalObat = \App\Models\Obat::count();
        $polis = \App\Models\Poli::withCount('dokters')->latest()->take(5)->get();
        return view('admin.dashboard', compact('totalDokter', 'totalPasien', 'totalPoli', 'totalObat', 'polis'));
    })->name('admin.dashboard');
    Route::resource('polis', AdminPoliController::class);
    Route::get('/dokter/export', [DokterController::class, 'exportExcel'])->name('dokter.export');
    Route::resource('dokter', DokterController::class);
    Route::get('/pasien/export', [PasienController::class, 'exportExcel'])->name('pasien.export');
    Route::resource('pasien', PasienController::class);
    Route::get('/obat/export', [ObatController::class, 'exportExcel'])->name('obat.export');
    Route::resource('obat', ObatController::class);
    Route::get('/pembayaran', [AdminPembayaranController::class, 'index'])->name('admin.pembayaran.index');
    Route::post('/pembayaran/{id}/konfirmasi', [AdminPembayaranController::class, 'konfirmasi'])->name('admin.pembayaran.konfirmasi');
});

// Dokter Routes
Route::middleware(['auth', 'role:dokter'])->prefix('dokter')->group(function () {
    Route::get('/dashboard', function () {
        $dokter = auth()->user()->load('poli');
        $jadwals = \App\Models\JadwalPeriksa::where('id_dokter', $dokter->id)->get();
        $totalPasienDiperiksa = \App\Models\Periksa::whereHas('daftarPoli.jadwalPeriksa', function ($q) use ($dokter) {
            $q->where('id_dokter', $dokter->id);
        })->count();
        $pasienMenunggu = \App\Models\DaftarPoli::whereIn('id_jadwal', $jadwals->pluck('id'))
            ->doesntHave('periksas')
            ->count();
        return view('dokter.dashboard', compact('dokter', 'jadwals', 'totalPasienDiperiksa', 'pasienMenunggu'));
    })->name('dokter.dashboard');

    Route::get('/jadwal-periksa/export', [JadwalPeriksaController::class, 'exportExcel'])
        ->name('jadwal-periksa.export');
    Route::resource('jadwal-periksa', JadwalPeriksaController::class);

    Route::get('/periksa-pasien', [PeriksaPasienController::class, 'index'])
        ->name('periksa-pasien.index');

    Route::post('/periksa-pasien', [PeriksaPasienController::class, 'store'])
        ->name('periksa-pasien.store');

    Route::get('/periksa-pasien/{id?}', [PeriksaPasienController::class, 'create'])
        ->name('periksa-pasien.create');

    Route::post('/periksa-pasien/{id}/panggil', [PeriksaPasienController::class, 'panggil'])
        ->name('dokter.panggil');

    Route::get('/riwayat-pasien/export', [RiwayatPasienController::class, 'exportExcel'])
        ->name('riwayat-pasien.export');
    Route::get('/riwayat-pasien', [RiwayatPasienController::class, 'index'])
        ->name('riwayat-pasien.index');

    Route::get('/riwayat-pasien/{id}', [RiwayatPasienController::class, 'detail'])
        ->name('riwayat-pasien.detail');

    Route::get('/riwayat-pasien/{id}/show', [RiwayatPasienController::class, 'show'])
        ->name('riwayat-pasien.show');
});

// Pasien Routes
Route::middleware(['auth', 'role:pasien'])->prefix('pasien')->group(function () {
    Route::get('/dashboard', [PasienDashboardController::class, 'index'])->name('pasien.dashboard');
    Route::get('/live-antrian', [PasienDashboardController::class, 'liveAntrian'])->name('pasien.antrian.live');

    Route::get('/daftar', [PasienPoliController::class, 'get'])->name('pasien.daftar');

    Route::post('/daftar', [PasienPoliController::class, 'submit'])
        ->name('pasien.daftar.submit');

    Route::get('/riwayat-pendaftaran', [RiwayatPendaftaranController::class, 'index'])
        ->name('pasien.riwayat.index');

    Route::get('/riwayat-pendaftaran/{id}', [RiwayatPendaftaranController::class, 'detail'])
        ->name('pasien.riwayat.detail');

    Route::get('/riwayat-pendaftaran/{id}/show', [RiwayatPendaftaranController::class, 'show'])
        ->name('pasien.riwayat.show');

    Route::get('/pembayaran', [PasienPembayaranController::class, 'index'])
        ->name('pasien.pembayaran.index');

    Route::post('/pembayaran/{id}/upload', [PasienPembayaranController::class, 'upload'])
        ->name('pasien.pembayaran.upload');
});