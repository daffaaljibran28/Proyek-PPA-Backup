@extends('pelapor.layout')

@section('title', 'Beranda')

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div><p class="text-sm text-slate-500">Beranda</p><h1 class="mt-1 text-2xl font-bold">Selamat datang, {{ auth()->user()->name }}</h1><p class="mt-2 text-sm text-slate-600">Kelola pengaduan dan pantau status penanganannya.</p></div>
        @if (!$laporanTerakhir || in_array($laporanTerakhir->status_penanganan, ['Selesai', 'Ditolak'], true))
            <a href="{{ route('pelapor.laporan.create') }}" class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">Buat Pengaduan</a>
        @endif
    </div>

    <section class="mt-7 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5"><p class="text-sm text-slate-500">Total Pengaduan</p><p class="mt-2 text-2xl font-bold">{{ $jumlahLaporan }}</p></div>
        <a href="{{ route('pelapor.laporan.index', ['status' => 'Menunggu Verifikasi']) }}" class="rounded-xl border border-amber-200 bg-white p-5 transition hover:border-amber-400 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-300"><p class="text-sm text-slate-500">Menunggu Verifikasi</p><p class="mt-2 text-2xl font-bold text-amber-700">{{ $menunggu }}</p></a>
        <a href="{{ route('pelapor.laporan.index', ['status' => 'Sedang Diproses']) }}" class="rounded-xl border border-blue-200 bg-white p-5 transition hover:border-blue-400 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-300"><p class="text-sm text-slate-500">Sedang Diproses</p><p class="mt-2 text-2xl font-bold text-blue-700">{{ $diproses }}</p></a>
        <a href="{{ route('pelapor.laporan.index', ['status' => 'Selesai']) }}" class="rounded-xl border border-emerald-200 bg-white p-5 transition hover:border-emerald-400 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-300"><p class="text-sm text-slate-500">Selesai</p><p class="mt-2 text-2xl font-bold text-emerald-700">{{ $selesai }}</p></a>
    </section>

    <section class="mt-7 rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
        <div class="flex items-center justify-between gap-3"><h2 class="font-bold">Pengaduan Terbaru</h2><a href="{{ route('pelapor.laporan.index') }}" class="text-sm font-semibold text-rose-700 hover:underline">Lihat semua</a></div>
        @if ($laporanTerakhir)
            <div class="mt-4 flex flex-col gap-4 rounded-lg border border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div><p class="text-sm font-bold text-rose-700">{{ $laporanTerakhir->nomor_laporan }}</p><p class="mt-1 text-sm font-medium">{{ $laporanTerakhir->kategori }}</p><p class="mt-1 text-xs text-slate-500">{{ optional($laporanTerakhir->tanggal_kejadian)->format('d M Y') }}</p></div>
                <div class="flex items-center gap-3"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $laporanTerakhir->status_penanganan === 'Selesai' ? 'bg-emerald-50 text-emerald-700' : ($laporanTerakhir->status_penanganan === 'Sedang Diproses' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700') }}">{{ $laporanTerakhir->status_penanganan }}</span><a href="{{ route('pelapor.laporan.show', $laporanTerakhir->id_laporan) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold hover:bg-slate-50">Detail</a></div>
            </div>
        @else
            <p class="mt-4 rounded-lg bg-slate-50 px-4 py-5 text-sm text-slate-600">Belum ada pengaduan. Pengaduan yang Anda buat akan tampil di sini.</p>
        @endif
    </section>

    <section class="mt-7 grid gap-6 lg:grid-cols-[1.5fr_1fr]">
        <div class="min-w-0 rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
            <h2 class="font-bold">Pembaruan Terbaru</h2>
            <div class="mt-4 divide-y divide-slate-100">
                @forelse ($pelaporNotifications->take(5) as $pembaruan)
                    <a href="{{ route('pelapor.laporan.show', $pembaruan['id_laporan']) }}" class="block min-w-0 py-3 first:pt-0">
                        <span class="block break-words text-sm font-semibold text-slate-800 [overflow-wrap:anywhere]">{{ $pembaruan['status'] ?? 'Pembaruan Pengaduan' }} · {{ $pembaruan['nomor_laporan'] }}</span>
                        <span class="mt-1 block break-words text-sm leading-5 text-slate-600 [overflow-wrap:anywhere]">{{ $pembaruan['pesan'] ?? $pembaruan['kategori'] }}</span>
                        @if (!empty($pembaruan['pada']))<time class="mt-1 block text-xs text-slate-500">{{ \Illuminate\Support\Carbon::parse($pembaruan['pada'])->format('d M Y, H:i') }}</time>@endif
                    </a>
                @empty
                    <p class="py-3 text-sm text-slate-500">Belum ada pembaruan status atau pesan.</p>
                @endforelse
            </div>
        </div>
        <div class="min-w-0 rounded-xl border border-rose-200 bg-rose-50 p-5 sm:p-6">
            <p class="text-sm font-semibold text-rose-800">Butuh bantuan segera?</p>
            <p class="mt-2 text-sm leading-6 text-slate-700">Hubungi layanan darurat yang tersedia kapan pun diperlukan.</p>
            <a href="{{ route('pelapor.information') }}" class="mt-4 inline-flex text-sm font-semibold text-rose-800 hover:underline">Lihat informasi layanan dan kontak</a>
        </div>
    </section>
@endsection