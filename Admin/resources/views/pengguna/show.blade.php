<x-app-layout>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('pengguna.index') }}" aria-label="Kembali ke daftar pengguna" title="Kembali ke daftar pengguna" class="grid h-10 w-10 place-items-center rounded-full border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:-translate-x-0.5 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7 7-7-7 7-7"/></svg>
        </a>
        <h2 class="text-xl font-bold text-rose-600">Profil & Riwayat Pengguna</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kartu Info Pengguna -->
        <div class="bg-white p-6 rounded-2xl border border-rose-100 shadow-sm h-fit">
            <div class="flex items-center gap-4 border-b pb-4 mb-4">
                <div class="w-14 h-14 rounded-full bg-rose-500 text-white flex items-center justify-center font-bold text-lg uppercase shadow-sm">
                    {{ substr($user->name ?? 'U', 0, 2) }}
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 text-base">{{ $user->name }}</h3>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-md {{ $user->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $user->is_active ? 'Akun Aktif' : 'Akun Nonaktif' }}
                    </span>
                </div>
            </div>

            <div class="space-y-4 text-xs">
                <div>
                    <p class="text-gray-400">Email</p>
                    <p class="font-semibold text-gray-700">{{ $user->email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Nomor HP / WhatsApp</p>
                    <p class="font-semibold text-gray-700">{{ $user->no_hp ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Tanggal Terdaftar</p>
                    <p class="font-semibold text-gray-700">{{ optional($user->created_at)->format('d F Y, H:i') ?? '-' }}</p>
                </div>
                <div class="pt-2 border-t border-gray-100">
                    <p class="text-gray-400">Total Kasus yang Dilaporkan</p>
                    <p class="font-bold text-rose-600 text-sm mt-0.5">{{ $laporans->count() }} Laporan</p>
                </div>
                <form method="POST" action="{{ route('pengguna.toggle-status', $user->id) }}" onsubmit="return confirm('{{ $user->is_active ? 'Nonaktifkan akun pengguna ini?' : 'Aktifkan kembali akun pengguna ini?' }}')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-full rounded-xl px-4 py-2.5 text-xs font-bold transition {{ $user->is_active ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                        {{ $user->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Kembali Akun' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Tabel Daftar Laporan dari Pengguna Ini -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-rose-100 shadow-sm overflow-hidden">
            <h3 class="font-bold text-rose-600 mb-4 border-b pb-2">Riwayat Laporan dari {{ $user->name }}</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-rose-50/50 text-gray-500 text-xs font-bold uppercase border-b border-rose-100">
                            <th class="py-3 px-4 whitespace-nowrap">No Laporan</th>
                            <th class="py-3 px-4 whitespace-nowrap">Kategori</th>
                            <th class="py-3 px-4 whitespace-nowrap">Tanggal</th>
                            <th class="py-3 px-4 text-center whitespace-nowrap">Status</th>
                            <th class="py-3 px-4 text-center whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @forelse($laporans as $item)
                        <tr>
                            <td class="py-4 px-4 font-bold text-rose-600 whitespace-nowrap">{{ $item->nomor_laporan }}</td>
                            <td class="py-4 px-4 font-semibold text-gray-800 whitespace-nowrap">{{ $item->kategori }}</td>
                            <td class="py-4 px-4 text-gray-500 whitespace-nowrap">{{ optional($item->tanggal_kejadian)->format('d M Y') ?? '-' }}</td>
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                @if($item->status_penanganan == 'Menunggu Verifikasi')
                                    <span class="inline-block bg-amber-50 text-amber-600 px-3 py-1 rounded-full font-bold">Menunggu Verifikasi</span>
                                @elseif($item->status_penanganan == 'Sedang Diproses' || $item->status_penanganan == 'Diproses')
                                    <span class="inline-block bg-blue-50 text-blue-600 px-3 py-1 rounded-full font-bold">Sedang Diproses</span>
                                @else
                                    <span class="inline-block bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full font-bold">Selesai</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                <a href="{{ route('laporan.show', $item->id_laporan) }}" class="inline-block bg-rose-500 text-white font-bold px-3 py-1.5 rounded-lg hover:bg-rose-600 transition shadow-sm">
                                    Lihat Kasus
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-gray-400">Pengguna ini belum memiliki riwayat laporan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>