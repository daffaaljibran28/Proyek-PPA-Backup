<x-app-layout>
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-medium flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold text-base px-1 cursor-pointer">&times;</button>
        </div>
    @endif

    <!-- Header Dashboard -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-rose-600 tracking-tight">Selamat datang, {{ Auth::user()->name ?? 'Admin' }}</h1>
            <p class="text-xs text-gray-500 mt-1">Rangkuman data dan status penanganan kasus terkini.</p>
        </div>
        <div class="flex items-center gap-2 bg-white px-3.5 py-2 rounded-xl border border-gray-200/80 text-xs font-medium text-gray-600 shadow-xs w-fit">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>{{ date('d F Y') }}</span>
        </div>
    </div>

    <!-- 4 Kartu Statistik -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Total Laporan -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs hover:border-gray-200 transition">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Laporan</span>
                <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $totalLaporan }}</h2>
            </div>
            <p class="text-[11px] text-gray-400 mt-1">Keseluruhan data laporan</p>
        </div>

        <!-- Menunggu Verifikasi -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs hover:border-gray-200 transition">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Menunggu Verifikasi</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $menungguVerifikasi }}</h2>
            </div>
            <p class="text-[11px] text-amber-600 font-medium mt-1">Memerlukan peninjauan</p>
        </div>

        <!-- Sedang Diproses -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs hover:border-gray-200 transition">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Sedang Diproses</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $sedangDiproses }}</h2>
            </div>
            <p class="text-[11px] text-blue-600 font-medium mt-1">Tindak lanjut tim lapangan</p>
        </div>

        <!-- Selesai -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs hover:border-gray-200 transition">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Telah Selesai</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $selesai }}</h2>
            </div>
            <p class="text-[11px] text-emerald-600 font-medium mt-1">Selesai ditangani</p>
        </div>
    </div>

    <!-- Tabel Laporan Masuk Terbaru -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-900 text-base">Laporan Masuk Terbaru</h3>
                <p class="text-xs text-gray-400 mt-0.5">Daftar laporan terbaru yang diterima sistem</p>
            </div>
            <a href="{{ route('laporan.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 bg-rose-50 px-3.5 py-2 rounded-xl hover:bg-rose-100 transition">
                <span>Lihat Semua</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100 text-gray-500 text-[11px] uppercase font-semibold tracking-wider">
                        <th class="py-3.5 px-6">No Laporan</th>
                        <th class="py-3.5 px-6">Kategori</th>
                        <th class="py-3.5 px-6">Pelapor</th>
                        <th class="py-3.5 px-6">Tanggal</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @forelse($laporanTerbaru as $item)
                    <tr class="hover:bg-gray-50/60 transition">
                        <td class="py-4 px-6 font-semibold text-rose-600">
                            <a href="{{ route('laporan.show', $item->id_laporan) }}" class="hover:underline">
                                {{ $item->nomor_laporan }}
                            </a>
                        </td>
                        <td class="py-4 px-6 text-gray-800 font-medium">{{ $item->kategori }}</td>
                        <td class="py-4 px-6 text-gray-600">{{ $item->nama_pelapor ?? 'Revitaaa' }}</td>
                        <td class="py-4 px-6 text-gray-500">{{ optional($item->tanggal_kejadian)->format('d M Y') ?? date('d M Y') }}</td>
                        <td class="py-4 px-6 text-center">
                            @if($item->status_penanganan == 'Menunggu Verifikasi')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Menunggu Verifikasi
                                </span>
                            @elseif($item->status_penanganan == 'Sedang Diproses' || $item->status_penanganan == 'Diproses')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/60">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    Sedang Diproses
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Selesai
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-400">Belum ada laporan masuk.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>