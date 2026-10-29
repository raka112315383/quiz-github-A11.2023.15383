<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil dan form ganti password untuk semua role.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user && $user->role === 'dokter' && $user->id_poli) {
            $user->load('poli');
        }

        return view('profile.index', compact('user'));
    }

    /**
     * Proses ubah password akun pengguna yang sedang login.
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        // Hanya mengubah password akun pengguna yang sedang login
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()
            ->route('profile.index')
            ->with('success', 'Password berhasil diganti!');
    }
}
