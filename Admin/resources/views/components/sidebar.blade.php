<aside class="w-64 bg-white border-r border-gray-100 flex flex-col justify-between p-6 min-h-screen">
    <div>
        <!-- Brand Logo -->
        <div class="flex items-center gap-3 mb-8">
            <div class="w-10 h-10 rounded-full bg-rose-500 flex items-center justify-center p-2 text-white shadow-md shadow-rose-200">
                <svg class="w-full h-full text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                    <path d="M12 5 9.04 7.96a2.17 2.17 0 0 0 0 3.08v0c.82.82 2.13.85 3 .07l2.07-1.9"/>
                    <path d="m14 10 1.5 1.5"/>
                </svg>
            </div>
            <div>
                <h1 class="font-extrabold text-rose-600 text-lg leading-none">SAPA</h1>
                <p class="text-xs text-gray-400">Sistem Pelaporan Aman</p>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="space-y-1.5">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-rose-500 text-white shadow-md shadow-rose-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('laporan.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition {{ request()->routeIs('laporan.*') ? 'bg-rose-500 text-white shadow-md shadow-rose-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Laporan Masuk</span>
            </a>

            <a href="{{ route('pengguna.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition {{ request()->routeIs('pengguna.*') ? 'bg-rose-500 text-white shadow-md shadow-rose-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>Manajemen Pengguna</span>
            </a>

            <!-- Menu Informasi & Edukasi Admin -->
            <a href="{{ route('admin.informasi.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.informasi.*') ? 'bg-rose-500 text-white shadow-md shadow-rose-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                <span>Manajemen Informasi & Edukasi</span>
            </a>

            <a href="{{ route('pengaturan.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition {{ request()->routeIs('pengaturan.*') ? 'bg-rose-500 text-white shadow-md shadow-rose-200' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Pengaturan Admin</span>
            </a>
        </nav>
    </div>

    <!-- Tombol Logout Langsung di Profil Bawah -->
    <div class="pt-4 border-t border-gray-100">
        <a href="{{ route('logout') }}" 
           onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?')"
           title="Klik untuk Keluar" 
           class="flex items-center justify-between p-2 rounded-2xl hover:bg-rose-50 transition group cursor-pointer">
            <div class="flex items-center gap-3">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'Admin SAPA') }}&background=f43f5e&color=fff&bold=true" class="w-10 h-10 rounded-full object-cover shadow-sm" alt="User Profile">
                <div>
                    <h4 class="font-bold text-sm text-gray-800 leading-tight group-hover:text-rose-600 transition">{{ Auth::user()->name ?? 'Admin' }}</h4>
                    <p class="text-xs text-gray-400">{{ Auth::user() && Auth::user()->isAdmin() ? 'Super Admin' : (ucfirst(Auth::user()->role ?? 'Admin')) }}</p>
                </div>
            </div>

            <!-- Ikon Pintu Keluar / Logout -->
            <div class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 group-hover:text-rose-600 group-hover:bg-rose-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </div>
        </a>
    </div>
</aside>