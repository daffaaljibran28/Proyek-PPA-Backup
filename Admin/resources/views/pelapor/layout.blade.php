<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sahabat PPA') | Pelapor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    <div class="min-h-screen md:flex">
        <aside class="hidden w-64 shrink-0 border-r border-rose-100 bg-white px-5 py-6 md:flex md:flex-col">
            <a href="{{ route('pelapor.home') }}" class="mb-9 flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-rose-600 text-lg font-bold text-white">S</span>
                <span><span class="block font-extrabold text-rose-700">Sahabat PPA</span><span class="text-xs text-slate-500">Ruang Pelapor</span></span>
            </a>
            <nav class="space-y-1 text-sm">
                <a href="{{ route('pelapor.home') }}" class="block rounded-lg px-3 py-2.5 {{ request()->routeIs('pelapor.home') ? 'bg-rose-50 font-semibold text-rose-700' : 'text-slate-600 hover:bg-slate-50' }}">Beranda</a>
                <details class="group rounded-lg" {{ request()->routeIs('pelapor.laporan.create') || request()->routeIs('pelapor.emergency') ? 'open' : '' }}>
                    <summary class="cursor-pointer list-none rounded-lg px-3 py-2.5 text-slate-600 hover:bg-slate-50">Buat Pengaduan <span class="float-right text-xs">⌄</span></summary>
                    <div class="ml-3 space-y-1 border-l border-rose-100 pl-3">
                        <a href="{{ route('pelapor.laporan.create') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('pelapor.laporan.create') ? 'bg-rose-50 font-semibold text-rose-700' : 'text-slate-600 hover:bg-slate-50' }}">Laporan Biasa</a>
                        <a href="{{ route('pelapor.emergency') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('pelapor.emergency') ? 'bg-rose-50 font-semibold text-rose-700' : 'text-slate-600 hover:bg-slate-50' }}">Emergency Call</a>
                    </div>
                </details>
                <a href="{{ route('pelapor.laporan.index') }}" class="block rounded-lg px-3 py-2.5 {{ request()->routeIs('pelapor.laporan.*') && !request()->routeIs('pelapor.laporan.create') ? 'bg-rose-50 font-semibold text-rose-700' : 'text-slate-600 hover:bg-slate-50' }}">Riwayat Pengaduan</a>
                <a href="{{ route('pelapor.information') }}" class="block rounded-lg px-3 py-2.5 {{ request()->routeIs('pelapor.information') ? 'bg-rose-50 font-semibold text-rose-700' : 'text-slate-600 hover:bg-slate-50' }}">Informasi & Edukasi</a>
                <a href="{{ route('pelapor.profile') }}" class="block rounded-lg px-3 py-2.5 {{ request()->routeIs('pelapor.profile') ? 'bg-rose-50 font-semibold text-rose-700' : 'text-slate-600 hover:bg-slate-50' }}">Profil</a>
            </nav>
            <div class="mt-auto border-t border-slate-100 pt-5">
                <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                <form method="POST" action="{{ route('pelapor.logout') }}" class="mt-4">@csrf<button class="text-sm font-medium text-rose-700 hover:underline">Keluar</button></form>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="border-b border-rose-100 bg-white px-4 py-4 sm:px-7 md:px-10">
                <div class="mx-auto flex max-w-6xl items-center justify-between gap-4">
                    <div><p class="text-xs font-medium text-rose-700">SAHABAT PPA</p><p class="text-sm font-semibold sm:text-base">Ruang Pelapor</p></div>
                    <span class="hidden text-sm text-slate-600 sm:block">Halo, {{ auth()->user()->name }}</span>
                    <details class="relative ml-auto sm:ml-0">
                        <summary aria-label="Notifikasi" title="Notifikasi" class="grid h-10 w-10 cursor-pointer list-none place-items-center rounded-lg border border-slate-200 text-slate-600 hover:bg-rose-50 hover:text-rose-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-4-5.7V5a2 2 0 1 0-4 0v.3A6 6 0 0 0 6 11v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 0 1-6 0m6 0H9"/></svg>
                            @if ($pelaporNotifications->contains(fn ($notification) => !$notification['read']))<span class="absolute right-1 top-1 grid h-4 min-w-4 place-items-center rounded-full bg-rose-600 px-1 text-[10px] font-bold text-white">{{ $pelaporNotifications->filter(fn ($notification) => !$notification['read'])->count() }}</span>@endif
                        </summary>
                        <div class="absolute right-0 z-30 mt-2 max-h-96 w-[min(22rem,calc(100vw-2rem))] overflow-y-auto rounded-xl border border-slate-200 bg-white p-3 shadow-xl">
                            <div class="flex items-center justify-between gap-2 px-2 py-1"><p class="text-sm font-bold">Pembaruan Pengaduan</p>
                                @if ($pelaporNotifications->contains(fn ($notification) => $notification['read']))
                                    <form method="POST" action="{{ route('pelapor.notifications.destroy-read') }}">@csrf @method('DELETE')<button class="text-xs font-semibold text-rose-700 hover:underline">Hapus semua yang sudah dibaca</button></form>
                                @endif
                            </div>
                            @forelse ($pelaporNotifications as $notification)
                                <div class="flex items-start gap-1 rounded-lg px-1 py-1 {{ $notification['read'] ? '' : 'bg-rose-50/70' }}">
                                    <form method="POST" action="{{ route('pelapor.notifications.read', [$notification['id_laporan'], $notification['event_index']]) }}" class="min-w-0 flex-1">
                                        @csrf
                                        <button type="submit" class="block w-full rounded-lg px-2 py-2 text-left hover:bg-rose-50">
                                            <span class="flex items-center justify-between gap-2"><span class="block break-words text-xs font-semibold text-rose-700">{{ $notification['nomor_laporan'] }} · {{ $notification['status'] ?? 'Pembaruan' }}</span><span class="shrink-0 text-[10px] {{ $notification['read'] ? 'text-slate-400' : 'font-semibold text-rose-700' }}">{{ $notification['read'] ? 'Sudah dibaca' : 'Belum dibaca' }}</span></span>
                                            @if (!empty($notification['pesan']))<span class="mt-1 block break-words text-sm text-slate-700 [overflow-wrap:anywhere]">{{ $notification['pesan'] }}</span>@else<span class="mt-1 block break-words text-xs text-slate-500 [overflow-wrap:anywhere]">{{ $notification['kategori'] }}</span>@endif
                                            <time class="mt-1 block text-xs text-slate-400">{{ !empty($notification['pada']) ? \Illuminate\Support\Carbon::parse($notification['pada'])->format('d M Y, H:i') : '' }}</time>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('pelapor.notifications.destroy', [$notification['id_laporan'], $notification['event_index']]) }}" class="shrink-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" aria-label="Hapus notifikasi" title="Hapus notifikasi" class="mt-1 grid h-8 w-8 place-items-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-700"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M18 6 6 18M6 6l12 12"/></svg></button>
                                    </form>
                                </div>
                            @empty
                                <p class="px-2 py-4 text-sm text-slate-500">Belum ada pembaruan status.</p>
                            @endforelse
                        </div>
                    </details>
                    <form method="POST" action="{{ route('pelapor.logout') }}" class="md:hidden">@csrf<button aria-label="Keluar" title="Keluar" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600">Keluar</button></form>
                </div>
                <nav class="mx-auto mt-4 flex max-w-6xl gap-2 overflow-x-auto whitespace-nowrap text-sm md:hidden">
                    <a href="{{ route('pelapor.home') }}" class="rounded-lg px-3 py-2 {{ request()->routeIs('pelapor.home') ? 'bg-rose-50 font-semibold text-rose-700' : 'text-slate-600' }}">Beranda</a>
                    <details class="relative">
                        <summary class="cursor-pointer list-none rounded-lg px-3 py-2 text-slate-600">Buat Pengaduan ⌄</summary>
                        <div class="absolute left-0 z-20 mt-1 min-w-44 rounded-lg border border-slate-200 bg-white p-1 shadow-lg"><a href="{{ route('pelapor.laporan.create') }}" class="block rounded-md px-3 py-2 text-slate-700 hover:bg-rose-50">Laporan Biasa</a><a href="{{ route('pelapor.emergency') }}" class="block rounded-md px-3 py-2 text-slate-700 hover:bg-rose-50">Emergency Call</a></div>
                    </details>
                    <a href="{{ route('pelapor.laporan.index') }}" class="rounded-lg px-3 py-2 text-slate-600">Riwayat</a>
                    <a href="{{ route('pelapor.information') }}" class="rounded-lg px-3 py-2 text-slate-600">Informasi & Edukasi</a>
                    <a href="{{ route('pelapor.profile') }}" class="rounded-lg px-3 py-2 text-slate-600">Profil</a>
                </nav>
            </header>

            <main class="mx-auto max-w-6xl px-4 py-7 sm:px-7 sm:py-9 md:px-10">
                @if (session('success'))
                    <div role="status" class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div role="alert" class="mb-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div role="alert" class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">Mohon periksa kembali isian formulir.</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>