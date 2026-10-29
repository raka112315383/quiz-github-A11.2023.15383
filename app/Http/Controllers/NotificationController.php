<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Tampilkan halaman daftar semua notifikasi milik pengguna yang sedang login.
     * Notifikasi hanya tampil untuk penerimanya.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $filter = $request->get('filter', 'all');

        $query = $user->notifications()->orderBy('created_at', 'desc');

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate(15)->withQueryString();
        $unreadCount = $user->unreadNotifications()->count();
        $totalCount = $user->notifications()->count();

        return view('notifikasi.index', compact('notifications', 'filter', 'unreadCount', 'totalCount'));
    }

    /**
     * Tandai satu notifikasi tertentu sebagai sudah dibaca.
     * Pengguna tidak boleh melihat atau menandai notifikasi milik pengguna lain.
     */
    public function markAsRead(string $id, Request $request): JsonResponse|RedirectResponse
    {
        // Hanya mencari notifikasi yang berelasi dengan pengguna yang sedang login
        $notification = Auth::user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi ditandai sudah dibaca.',
            ]);
        }

        return redirect()->back()->with('success', 'Notifikasi berhasil ditandai sudah dibaca.');
    }

    /**
     * Tandai seluruh notifikasi yang belum dibaca sebagai sudah dibaca.
     */
    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi berhasil ditandai sudah dibaca.',
            ]);
        }

        return redirect()->back()->with('success', 'Semua notifikasi berhasil ditandai sebagai sudah dibaca.');
    }
}
