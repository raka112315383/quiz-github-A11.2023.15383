<x-layouts.app title="Semua Notifikasi">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Notifikasi & Pemberitahuan
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Daftar semua pemberitahuan aktivitas akun Anda diurutkan dari yang terbaru.
            </p>
        </div>

        @if($unreadCount > 0)
        <form action="{{ route('notifikasi.markAllAsRead') }}" method="POST">
            @csrf
            <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white font-semibold text-xs transition shadow-sm">
                <i class="fas fa-check-double"></i>
                <span>Tandai Semua Sudah Dibaca</span>
            </button>
        </form>
        @endif
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="alert alert-success bg-emerald-50 text-emerald-800 border border-emerald-200 mb-6 rounded-2xl shadow-sm flex items-center gap-3 p-4">
        <i class="fas fa-circle-check text-emerald-500 text-lg"></i>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Filter Tabs --}}
    <div class="flex items-center gap-2 mb-6">
        <a href="{{ route('notifikasi.index', ['filter' => 'all']) }}"
            class="px-4 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-2 {{ $filter !== 'unread' ? 'bg-primary text-white shadow-sm' : 'bg-base-100 hover:bg-slate-100 text-slate-600 border border-slate-200' }}">
            <span>Semua</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $filter !== 'unread' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                {{ $totalCount }}
            </span>
        </a>

        <a href="{{ route('notifikasi.index', ['filter' => 'unread']) }}"
            class="px-4 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-2 {{ $filter === 'unread' ? 'bg-primary text-white shadow-sm' : 'bg-base-100 hover:bg-slate-100 text-slate-600 border border-slate-200' }}">
            <span>Belum Dibaca</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $filter === 'unread' ? 'bg-white/20 text-white' : 'bg-indigo-100 text-indigo-700' }}">
                {{ $unreadCount }}
            </span>
        </a>
    </div>

    {{-- List Card --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200 overflow-hidden">
        <div class="divide-y divide-slate-100">
            @forelse($notifications as $notif)
            @php
                $isUnread = is_null($notif->read_at);
                $data = $notif->data ?? [];
                $title = $data['title'] ?? 'Pemberitahuan';
                $message = $data['message'] ?? '';
                $url = $data['url'] ?? null;
                $icon = $data['icon'] ?? 'fa-bell text-indigo-500';
            @endphp
            <div class="p-5 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $isUnread ? 'bg-indigo-50/60 hover:bg-indigo-50 border-l-4 border-indigo-600' : 'bg-base-100 hover:bg-slate-50 border-l-4 border-transparent opacity-90' }}">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-sm flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
                        <i class="fas {{ $icon }}"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm {{ $isUnread ? 'font-bold text-slate-900' : 'font-semibold text-slate-700' }}">
                                {{ $title }}
                            </h3>
                            @if($isUnread)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-600 text-white shadow-xs">
                                Baru
                            </span>
                            @else
                            <span class="text-[11px] text-slate-400 font-normal">
                                (Sudah dibaca)
                            </span>
                            @endif
                        </div>

                        <p class="text-xs text-slate-600 mt-1 leading-relaxed max-w-2xl">
                            {{ $message }}
                        </p>

                        <div class="text-[11px] text-slate-400 mt-2 flex items-center gap-1">
                            <i class="fas fa-clock text-[10px]"></i>
                            <span>{{ $notif->created_at->translatedFormat('d F Y, H:i') }}</span>
                            <span class="mx-1">•</span>
                            <span>{{ $notif->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                    @if($isUnread)
                    <form action="{{ route('notifikasi.markAsRead', $notif->id) }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition"
                            title="Tandai notifikasi ini sudah dibaca">
                            <i class="fas fa-check text-xs"></i>
                            <span>Tandai Dibaca</span>
                        </button>
                    </form>
                    @endif

                    @if($url)
                    <a href="{{ $url }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-primary hover:bg-primary/90 text-white text-xs font-semibold transition shadow-sm">
                        <span>Buka Halaman</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                    @endif
                </div>
            </div>
            @empty
            <div class="p-16 text-center text-slate-400">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="fas fa-bell-slash"></i>
                </div>
                <h3 class="font-bold text-slate-700 text-base mb-1">Belum Ada Notifikasi</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    {{ $filter === 'unread' ? 'Tidak ada notifikasi yang belum dibaca.' : 'Saat ini Anda belum menerima pemberitahuan aktivitas apa pun.' }}
                </p>
            </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50/60">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>

</x-layouts.app>
