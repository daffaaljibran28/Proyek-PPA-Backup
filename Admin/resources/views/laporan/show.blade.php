<x-app-layout>
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('laporan.index') }}" class="w-9 h-9 border border-gray-200 bg-white rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-50 transition">←</a>
            <h2 class="text-xl font-bold text-rose-600">Detail Laporan {{ $laporan->nomor_laporan }}</h2>
        </div>
        <span class="bg-amber-50 text-amber-600 border border-amber-200 px-4 py-1.5 rounded-full text-xs font-bold">
            {{ $laporan->status_penanganan }}
        </span>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-xl font-semibold">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-amber-50 border border-amber-200 text-amber-800 text-xs rounded-xl font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid min-w-0 grid-cols-1 gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)]">
        <!-- Kolom Data Utama -->
        <div class="min-w-0 space-y-6">
            <div class="min-w-0 rounded-2xl border border-rose-100 bg-white p-6 shadow-sm">
                <h3 class="font-bold text-rose-600 mb-4 border-b pb-2">Data Utama Laporan</h3>
                <div class="grid grid-cols-2 gap-y-4 text-sm">
                    <div>
                        <p class="text-gray-400 text-xs">Nomor Laporan</p>
                        <p class="font-bold text-rose-600">{{ $laporan->nomor_laporan }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Kategori Kejadian</p>
                        <p class="font-semibold text-gray-800">{{ $laporan->kategori }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Nama Pelapor</p>
                        <p class="font-semibold text-gray-800">{{ $laporan->nama_pelapor ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Melapor Sebagai</p>
                        <p class="font-semibold text-gray-800">{{ $laporan->sebagai }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Lokasi Kejadian</p>
                        <p class="font-semibold text-gray-800">{{ $laporan->lokasi_kejadian }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Tanggal Kejadian</p>
                        <p class="font-semibold text-gray-800">
                            {{ optional($laporan->tanggal_kejadian)->format('d M Y') ?? '-' }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 min-w-0 border-t border-gray-100 pt-4">
                    <p class="font-bold text-gray-800 text-sm mb-2">Penjelasan Kronologi Kejadian</p>
                    <p class="min-w-0 max-w-full whitespace-pre-wrap break-words [overflow-wrap:anywhere] rounded-xl bg-gray-50 p-4 text-sm leading-relaxed text-gray-600">
                        {{ $laporan->kronologi }}
                    </p>
                </div>
            </div>

            <!-- Bukti Lampiran -->
            <div class="bg-white p-6 rounded-2xl border border-rose-100 shadow-sm">
                <h3 class="font-bold text-rose-600 mb-4">Bukti Lampiran</h3>
                @if($laporan->bukti_lampiran)
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach(array_values(array_filter(array_map('trim', explode(',', $laporan->bukti_lampiran)))) as $index => $path)
                            <a href="{{ str_starts_with($path, 'laporan/' . $laporan->id_laporan . '/') ? route('laporan.evidence', [$laporan->id_laporan, $index]) : asset('storage/' . $path) }}" target="_blank" rel="noopener" class="block">
                                @if(str_starts_with($path, 'laporan/' . $laporan->id_laporan . '/'))
                                    <img src="{{ route('laporan.evidence', [$laporan->id_laporan, $index]) }}" alt="Bukti foto pengaduan" class="aspect-square w-full rounded-lg border border-gray-200 object-cover">
                                @else
                                    <span class="block break-all rounded-lg border border-gray-200 p-3 text-sm text-gray-700">{{ $path }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-400 text-xs italic">Tidak ada bukti lampiran yang disertakan.</p>
                @endif
            </div>
        </div>

        <!-- Kolom Update Status -->
        <div class="min-w-0 space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-rose-100 shadow-sm">
                <h3 class="font-bold text-rose-600 mb-4">Tindak Lanjut Laporan</h3>
                @if ($laporan->status_penanganan === 'Selesai')
                    <p class="rounded-xl bg-emerald-50 p-4 text-sm leading-6 text-emerald-800">Laporan sudah selesai. Data dan status dikunci dan hanya dapat dilihat.</p>
                @else
                <form action="{{ route('laporan.update-status', $laporan->id_laporan) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Ubah Status</label>
                        <select name="status_penanganan" class="w-full border-gray-200 rounded-xl text-xs p-3 focus:ring-rose-500 focus:border-rose-500">
                            <option value="Menunggu Verifikasi" {{ $laporan->status_penanganan == 'Menunggu Verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                            <option value="Sedang Diproses" {{ $laporan->status_penanganan == 'Sedang Diproses' ? 'selected' : '' }}>Sedang Diproses / Diverifikasi</option>
                            @if ($laporan->status_penanganan === 'Diproses')<option value="Diproses" selected>Diproses (status lama)</option>@endif
                            <option value="Selesai">Selesai</option>
                            <option value="Ditolak" {{ $laporan->status_penanganan == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Catatan internal (tidak terlihat pelapor)</label>
                        <textarea name="catatan_admin" rows="3" class="w-full border-gray-200 rounded-xl text-xs p-3 focus:ring-rose-500 focus:border-rose-500" placeholder="Catatan internal petugas...">{{ $laporan->catatan_admin }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Pesan untuk Pelapor</label>
                        <textarea name="pesan_pelapor" rows="3" maxlength="2000" class="w-full border-gray-200 rounded-xl text-xs p-3 focus:ring-rose-500 focus:border-rose-500" placeholder="Pesan yang dapat dibaca pelapor...">{{ old('pesan_pelapor') }}</textarea>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="reset" class="w-1/2 py-2.5 border border-rose-500 text-rose-500 rounded-xl text-xs font-bold hover:bg-rose-50">Batal</button>
                        <button type="submit" class="w-1/2 py-2.5 bg-rose-500 text-white rounded-xl text-xs font-bold hover:bg-rose-600 shadow-md shadow-rose-200">Simpan</button>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>