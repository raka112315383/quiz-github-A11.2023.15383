<?php

use App\Http\Controllers\Admin\AdminPoliController;
use App\Http\Controllers\Admin\DokterController;
use App\Http\Controllers\Admin\ObatController;
use App\Http\Controllers\Admin\PasienController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dokter\JadwalPeriksaController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');



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
    Route::resource('dokter', DokterController::class);
    Route::resource('pasien', PasienController::class);
    Route::resource('obat', ObatController::class);
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
    Route::resource('jadwal-periksa', JadwalPeriksaController::class);
});

// Pasien Routes
Route::middleware(['auth', 'role:pasien'])->prefix('pasien')->group(function () {
    Route::get('/dashboard', function () {
        $pasien = auth()->user();
        $daftarPolis = \App\Models\DaftarPoli::with(['jadwalPeriksa.dokter.poli', 'periksas'])
            ->where('id_pasien', $pasien->id)
            ->latest()
            ->take(5)
            ->get();
        $totalKunjungan = \App\Models\DaftarPoli::where('id_pasien', $pasien->id)->count();
        return view('pasien.dashboard', compact('pasien', 'daftarPolis', 'totalKunjungan'));
    })->name('pasien.dashboard');
});