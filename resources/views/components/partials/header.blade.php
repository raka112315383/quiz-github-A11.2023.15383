<header class="bg-base-100 border-b border-base-300 h-16 flex items-center px-6 gap-4 sticky top-0 z-30 shadow-sm">

    {{-- Mobile Hamburger --}}
    <button onclick="toggleSidebar()" class="btn btn-square btn-ghost lg:hidden">
        <i class="fas fa-bars text-lg"></i>
    </button>

    {{-- Breadcrumb --}}
    <div class="flex-1">
        <div class="flex items-center gap-2 text-sm">
            <span class="text-base-content/50">Poliklinik</span>
            <i class="fas fa-chevron-right text-xs text-base-content/30"></i>
            <span class="font-semibold text-base-content">
                {{ $title ?? 'Dashboard' }}
            </span>
        </div>
    </div>

    {{-- Fullscreen --}}
    <button onclick="toggleFullscreen()" class="btn btn-square btn-ghost">
        <i id="fsIcon" class="fas fa-expand w-5 h-5"></i>
    </button>

    {{-- Notification Dropdown --}}
    @auth
    @php
        $authUser = auth()->user();
        $unreadCount = $authUser->unreadNotifications()->count();
        $recentNotifications = $authUser->notifications()->take(5)->get();
    @endphp
    <div class="dropdown dropdown-end">
        <div tabindex="0" role="button" class="btn btn-square btn-ghost relative" title="Notifikasi">
            <i class="fas fa-bell text-lg text-slate-600"></i>
            @if($unreadCount > 0)
            <span class="badge badge-primary badge-xs absolute top-2 right-2 font-bold px-1.5 py-0.5 rounded-full shadow-sm animate-pulse">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
            @endif
        </div>

        <div tabindex="0" class="dropdown-content menu z-50 mt-3 p-0 shadow-2xl bg-base-100 rounded-2xl w-80 sm:w-96 border border-slate-200 overflow-hidden">
            {{-- Header Dropdown --}}
            <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-800 text-sm">Notifikasi</span>
                    @if($unreadCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700">
                        {{ $unreadCount }} Baru
                    </span>
                    @endif
                </div>

                @if($unreadCount > 0)
                <form action="{{ route('notifikasi.markAllAsRead') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="text-[11px] text-primary hover:underline font-semibold flex items-center gap-1">
                        <i class="fas fa-check-double text-[10px]"></i> Tandai semua dibaca
                    </button>
                </form>
                @endif
            </div>

            {{-- List Notifikasi --}}
            <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                @forelse($recentNotifications as $notif)
                @php
                    $isUnread = is_null($notif->read_at);
                    $data = $notif->data ?? [];
                    $title = $data['title'] ?? 'Pemberitahuan';
                    $message = $data['message'] ?? '';
                    $url = $data['url'] ?? null;
                    $icon = $data['icon'] ?? 'fa-bell text-indigo-500';
                @endphp
                <div class="p-3.5 transition {{ $isUnread ? 'bg-indigo-50/50 hover:bg-indigo-50 border-l-4 border-indigo-600' : 'bg-base-100 hover:bg-slate-50 border-l-4 border-transparent' }}">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-xs flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
                            <i class="fas {{ $icon }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-xs {{ $isUnread ? 'font-bold text-slate-900' : 'font-medium text-slate-700' }} truncate">
                                    {{ $title }}
                                </span>
                                @if($isUnread)
                                <span class="w-2 h-2 rounded-full bg-indigo-600 shrink-0" title="Belum dibaca"></span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5 line-clamp-2 leading-relaxed">
                                {{ $message }}
                            </p>
                            <div class="flex items-center justify-between mt-2 pt-1">
                                <span class="text-[10px] text-slate-400 font-medium">
                                    <i class="fas fa-clock text-[9px]"></i> {{ $notif->created_at->diffForHumans() }}
                                </span>
                                <div class="flex items-center gap-2">
                                    @if($isUnread)
                                    <form action="{{ route('notifikasi.markAsRead', $notif->id) }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="text-[11px] text-indigo-600 hover:text-indigo-800 font-semibold" title="Tandai sudah dibaca">
                                            Tandai dibaca
                                        </button>
                                    </form>
                                    @endif
                                    @if($url)
                                    <a href="{{ $url }}" class="text-[11px] text-primary hover:underline font-semibold flex items-center gap-0.5">
                                        Buka <i class="fas fa-chevron-right text-[8px]"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-slate-400">
                    <i class="fas fa-bell-slash text-2xl mb-2 text-slate-300 block"></i>
                    <p class="text-xs font-semibold text-slate-600">Belum Ada Notifikasi</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Anda belum memiliki pemberitahuan baru saat ini.</p>
                </div>
                @endforelse
            </div>

            {{-- Footer Dropdown --}}
            <div class="p-2.5 bg-slate-50 border-t border-slate-200 text-center">
                <a href="{{ route('notifikasi.index') }}" class="text-xs text-primary hover:underline font-semibold inline-flex items-center gap-1.5">
                    <span>Lihat Semua Notifikasi</span>
                    <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
    </div>
    @endauth

    {{-- User Info --}}
    <a href="{{ route('profile.index') }}" class="flex items-center gap-3 p-1 rounded-xl hover:bg-base-200 transition group cursor-pointer" title="Lihat Profil & Ganti Password">

        <div class="text-right hidden sm:block">
            <div class="text-sm font-semibold leading-tight group-hover:text-primary transition">
                {{ auth()->user()->nama ?? auth()->user()->name ?? 'Pengguna' }}
            </div>
            <div class="text-xs text-base-content/50 leading-tight">
                {{ ucfirst(auth()->user()->role ?? 'Pengguna') }}
            </div>
        </div>

        <div class="avatar">
            <div class="w-10 h-10 rounded-full bg-primary text-primary-content flex items-center justify-center font-bold shadow-sm group-hover:ring-2 group-hover:ring-primary/40 transition">
                <span class="text-sm font-semibold leading-none">
                    {{ strtoupper(substr(auth()->user()->nama ?? auth()->user()->name ?? 'U', 0, 1)) }}
                </span>
            </div>
        </div>
    </a>

</header>

<script>
    function toggleFullscreen() {
    const icon = document.getElementById('fsIcon');

    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
        icon.classList.remove('fa-expand');
        icon.classList.add('fa-compress');
    } else {
        document.exitFullscreen();
        icon.classList.remove('fa-compress');
        icon.classList.add('fa-expand');
    }
}
</script>