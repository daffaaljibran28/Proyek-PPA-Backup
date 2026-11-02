<x-app-layout>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-rose-600">Laporan Masuk</h2>
        <span class="bg-rose-500 text-white text-xs font-bold px-4 py-2 rounded-xl">Total: {{ $laporans->total() }} Kasus</span>
    </div>

    <!-- Filter & Search Bar -->
    <form method="GET" action="{{ route('laporan.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="relative md:col-span-1">
            <input type="text" name="search" value="{{ request('search') }}" autocomplete="off" placeholder="Cari Nama Pelapor..." class="w-full pl-9 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs focus:ring-rose-500 focus:border-rose-500">
            <span class="absolute left-3 top-3 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
        </div>

        <select name="kategori" onchange="this.form.submit()" class="bg-white border border-rose-100 text-xs rounded-xl p-2.5 text-gray-600">
            <option value="">Semua Kategori</option>
            <option value="Kekerasan terhadap Perempuan" {{ str_contains(request('kategori'), 'Perempuan') ? 'selected' : '' }}>Kekerasan Perempuan</option>
            <option value="KDRT" {{ request('kategori') == 'KDRT' || str_contains(request('kategori'), 'Rumah Tangga') ? 'selected' : '' }}>KDRT (Rumah Tangga)</option>
            <option value="Kekerasan terhadap Anak" {{ str_contains(request('kategori'), 'Anak') ? 'selected' : '' }}>Kekerasan Anak</option>
            <option value="Seksual" {{ str_contains(request('kategori'), 'Seksual') ? 'selected' : '' }}>Pelecehan / Kekerasan Seksual</option>
            <option value="Perundungan" {{ str_contains(request('kategori'), 'Perundungan') || str_contains(request('kategori'), 'Bullying') ? 'selected' : '' }}>Perundungan (Bullying)</option>
        </select>

        <select name="status" onchange="this.form.submit()" class="bg-white border border-rose-100 text-xs rounded-xl p-2.5 text-gray-600">
            <option value="">Semua Status</option>
            <option value="Menunggu Verifikasi" {{ request('status') == 'Menunggu Verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
            <option value="Sedang Diproses" {{ request('status') == 'Sedang Diproses' ? 'selected' : '' }}>Sedang Diproses</option>
            <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
            <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
        </select>

        <button type="submit" class="bg-rose-50 border border-rose-200 text-rose-600 text-xs font-bold py-2.5 rounded-xl hover:bg-rose-100">Filter Data</button>
    </form>

    <!-- Table Card -->
    <div class="bg-white p-6 rounded-2xl border border-rose-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full table-fixed text-left border-collapse">
                <colgroup>
                    <col class="w-[14%]">
                    <col class="w-[17%]">
                    <col class="w-[12%]">
                    <col class="w-[22%]">
                    <col class="w-[11%]">
                    <col class="w-[16%]">
                    <col class="w-[8%]">
                </colgroup>
                <thead>
                    <tr class="bg-rose-50/50 text-gray-500 text-xs font-bold uppercase border-b border-rose-100">
                        <th class="py-3 px-2 md:px-3">No Laporan</th>
                        <th class="py-3 px-2 md:px-3">Kategori</th>
                        <th class="py-3 px-2 md:px-3">Nama Pelapor</th>
                        <th class="py-3 px-2 md:px-3">Lokasi</th>
                        <th class="py-3 px-2 md:px-3">Tanggal</th>
                        <th class="py-3 px-2 md:px-3 text-center">Status</th>
                        <th class="py-3 px-2 md:px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @forelse($laporans as $item)
                    <tr>
                        <td class="py-4 px-2 md:px-3 font-bold text-rose-600 break-all">{{ $item->nomor_laporan }}</td>
                        <td class="py-4 px-2 md:px-3 font-semibold text-gray-800 break-words">{{ $item->kategori }}</td>
                        <td class="py-4 px-2 md:px-3 font-medium text-gray-700 break-words">
                            {{ $item->nama_pelapor ?? '-' }}
                        </td>
                        <td class="py-4 px-2 md:px-3 text-gray-600 break-words">{{ $item->lokasi_kejadian }}</td>
                        <td class="py-4 px-2 md:px-3 text-gray-500">{{ optional($item->tanggal_kejadian)->format('d M Y') ?? '-' }}</td>
                        <td class="py-4 px-2 md:px-3 text-center">
                            @if($item->status_penanganan == 'Menunggu Verifikasi')
                                <span class="inline-flex max-w-full justify-center rounded-full bg-amber-50 px-2 md:px-3 py-1 text-center font-bold text-amber-600">Menunggu Verifikasi</span>
                            @elseif($item->status_penanganan == 'Sedang Diproses' || $item->status_penanganan == 'Diproses')
                                <span class="inline-flex max-w-full justify-center rounded-full bg-blue-50 px-2 md:px-3 py-1 text-center font-bold text-blue-600">Sedang Diproses</span>
                            @elseif($item->status_penanganan == 'Selesai')
                                <span class="inline-flex max-w-full justify-center rounded-full bg-emerald-50 px-2 md:px-3 py-1 text-center font-bold text-emerald-600">Selesai</span>
                            @elseif($item->status_penanganan == 'Ditolak')
                                <span class="inline-flex max-w-full justify-center rounded-full bg-rose-50 px-2 md:px-3 py-1 text-center font-bold text-rose-600">Ditolak</span>
                            @else
                                <span class="inline-flex max-w-full justify-center rounded-full bg-gray-100 px-2 md:px-3 py-1 text-center font-bold text-gray-600">{{ $item->status_penanganan }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-2 md:px-3 text-center">
                            <a href="{{ route('laporan.show', $item->id_laporan) }}" class="inline-block rounded-lg bg-rose-500 px-2 md:px-3 py-1.5 font-bold text-white transition hover:bg-rose-600">Lihat</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-400">Belum ada data laporan masuk.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $laporans->links() }}
        </div>
    </div>
</x-app-layout>