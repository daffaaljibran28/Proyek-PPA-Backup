<x-app-layout>
    <div class="space-y-6">
        <!-- Header Page & Total Informasi -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-rose-600 tracking-tight">Manajemen Informasi & Edukasi</h2>
            </div>
            <span class="bg-rose-500 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-sm">
                Total: {{ $informasis->total() }} Informasi
            </span>
        </div>

        <!-- Filter & Tombol Tambah (Filter dikompakkan di kiri, Tombol Tambah diperbesar di kanan) -->
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 mb-6">
            <!-- Form Filter (Kiri) -->
            <form method="GET" action="{{ route('admin.informasi.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
                <!-- Search Bar (Lebih kecil/kompak) -->
                <div class="relative w-full md:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" autocomplete="off" placeholder="Cari judul informasi..." 
                        class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-xs text-gray-700 focus:ring-rose-500 focus:border-rose-500 shadow-sm">
                    <span class="absolute left-3 top-2.5 text-gray-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                </div>

                <!-- Filter Status -->
                <select name="status" onchange="this.form.submit()" class="bg-white border border-rose-100 text-xs rounded-xl py-2 px-3 text-gray-600 focus:ring-rose-500 focus:border-rose-500 shadow-sm">
                    <option value="">Semua Status</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draf</option>
                </select>
            </form>

            <!-- Tombol Tambah Informasi (Kanan - Lebih Besar & Menonjol) -->
            <a href="{{ route('admin.informasi.create') }}" class="bg-rose-500 text-white text-xs font-bold px-5 py-2.5 rounded-xl hover:bg-rose-600 transition inline-flex items-center justify-center shadow-md shadow-rose-500/20 shrink-0">
                + Tambah Informasi
            </a>
        </div>

        <!-- Alert Success -->
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-800 mb-6">
                {{ session('success') }}
            </div>
        @endif

        <!-- Table Card -->
        <div class="bg-white p-6 rounded-3xl border border-rose-100/70 shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-rose-50/50 text-gray-500 text-xs font-bold uppercase border-b border-rose-100">
                        <th class="py-3 px-4">No</th>
                        <th class="py-3 px-4">Judul Informasi</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Tanggal Publish</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @forelse($informasis as $index => $item)
                    <tr class="hover:bg-rose-100/50 transition">
                        <td class="py-4 px-4 font-semibold text-gray-800">
                            {{ $informasis->firstItem() + $index }}
                        </td>
                        <td class="py-4 px-4 font-bold text-gray-900">{{ $item->judul }}</td>
                        <td class="py-4 px-4">
                            @if($item->status == 'published')
                                <span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full font-bold">Published</span>
                            @else
                                <span class="bg-rose-50 text-rose-600 px-3 py-1 rounded-full font-bold">Draf</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-gray-500">
                            {{ $item->tanggal_publish ? \Carbon\Carbon::parse($item->tanggal_publish)->format('d M Y') : '-' }}
                        </td>
                        <td class="py-4 px-4 text-center">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.informasi.edit', $item->id) }}" class="bg-slate-100 text-slate-700 font-bold px-3 py-1.5 rounded-lg hover:bg-slate-200 transition">Edit</a>
                                <form action="{{ route('admin.informasi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus informasi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-rose-50 text-rose-600 border border-rose-200 font-bold px-3 py-1.5 rounded-lg hover:bg-rose-100 transition">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-gray-400">Belum ada data informasi atau edukasi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $informasis->links() }}
            </div>
        </div>
    </div>
</x-app-layout>