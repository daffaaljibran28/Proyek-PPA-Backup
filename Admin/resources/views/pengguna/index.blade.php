<x-app-layout>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-rose-600">Manajemen Pengguna</h2>
        <div class="relative w-72">
            <input type="text" placeholder="Cari nama, email..." class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-xs focus:ring-rose-500 focus:border-rose-500 shadow-sm">
            <span class="absolute left-3 top-2.5 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-rose-100 shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-rose-50/50 text-gray-500 text-xs font-bold uppercase border-b border-rose-100">
                    <th class="py-3 px-4">Nama Lengkap</th>
                    <th class="py-3 px-4">Email</th>
                    <th class="py-3 px-4">Nomor HP</th>
                    <th class="py-3 px-4">Tanggal Daftar</th>
                    <th class="py-3 px-4">Total Laporan</th>
                    <th class="py-3 px-4 text-center">Status Akun</th>
                    <th class="py-3 px-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-xs">
                @forelse($penggunas as $user)
                <tr class="hover:bg-rose-50/30 transition">
                    <td class="py-4 px-4 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-rose-500 text-white flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                            {{ substr($user->name ?? $user->nama ?? 'U', 0, 2) }}
                        </div>
                        <span class="font-semibold text-gray-800">{{ $user->name ?? $user->nama ?? '-' }}</span>
                    </td>
                    <td class="py-4 px-4 text-gray-600">{{ $user->email ?? '-' }}</td>
                    <td class="py-4 px-4 text-gray-600">{{ $user->no_hp ?? $user->telepon ?? $user->nomor_hp ?? '-' }}</td>
                    <td class="py-4 px-4 text-gray-500">{{ optional($user->created_at)->format('d M Y') ?? '-' }}</td>
                    <td class="py-4 px-4 font-bold text-rose-600">
                        {{ \App\Models\Laporan::where('id_pengguna', $user->id ?? $user->id_pengguna)->count() }} Laporan
                    </td>
                    <td class="py-4 px-4 text-center">
                        <span class="inline-flex rounded-full px-3 py-1 font-bold {{ $user->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="py-4 px-4">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('pengguna.show', $user->id) }}" class="text-rose-500 font-bold hover:underline cursor-pointer">Detail</a>
                            <form method="POST" action="{{ route('pengguna.toggle-status', $user->id) }}" onsubmit="return confirm('{{ $user->is_active ? 'Nonaktifkan akun pengguna ini?' : 'Aktifkan kembali akun pengguna ini?' }}')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="font-bold hover:underline {{ $user->is_active ? 'text-amber-600' : 'text-emerald-600' }}">
                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-8 text-gray-400">Belum ada data pengguna terdaftar di database.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>