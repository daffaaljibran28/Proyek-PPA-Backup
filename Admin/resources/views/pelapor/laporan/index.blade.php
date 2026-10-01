@extends('pelapor.layout')

@section('title', 'Riwayat Pengaduan')

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div><p class="text-sm text-slate-500">Pengaduan</p><h1 class="mt-1 text-2xl font-bold">Riwayat Pengaduan</h1><p class="mt-2 text-sm text-slate-600">Daftar pengaduan yang dikirim dari akun Anda.</p></div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <form method="GET" action="{{ route('pelapor.laporan.index') }}" class="flex gap-2">
                <label for="status" class="sr-only">Filter status</label>
                <select id="status" name="status" class="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    @foreach (['Menunggu Verifikasi', 'Sedang Diproses', 'Selesai', 'Ditolak'] as $option)
                        <option value="{{ $option }}" @selected($status === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </form>
            @if ($canCreateReport)
                <a href="{{ route('pelapor.laporan.create') }}" class="rounded-lg bg-rose-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-rose-700">Buat Pengaduan</a>
            @endif
        </div>
    </div>

    <section class="mt-6 space-y-3">
        @forelse ($laporans as $laporan)
            <article class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <a href="{{ route('pelapor.laporan.show', $laporan->id_laporan) }}" class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-rose-700">{{ $laporan->nomor_laporan }}</p>
                        <p class="mt-1 font-semibold">{{ $laporan->kategori }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $laporan->lokasi_kejadian }} · {{ optional($laporan->tanggal_kejadian)->format('d M Y') }}</p>
                    </a>
                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $laporan->status_penanganan === 'Selesai' ? 'bg-emerald-50 text-emerald-700' : ($laporan->status_penanganan === 'Sedang Diproses' || $laporan->status_penanganan === 'Diproses' ? 'bg-blue-50 text-blue-700' : ($laporan->status_penanganan === 'Ditolak' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700')) }}">{{ $laporan->status_penanganan }}</span>
                        <a href="{{ route('pelapor.laporan.show', $laporan->id_laporan) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium hover:bg-slate-50">Detail</a>
                        @if ($laporan->status_penanganan === 'Menunggu Verifikasi')
                            <a href="{{ route('pelapor.laporan.edit', $laporan->id_laporan) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium hover:bg-slate-50">Edit</a>
                        @else
                            <span aria-disabled="true" title="Pengaduan hanya dapat diedit saat Menunggu Verifikasi" class="cursor-not-allowed rounded-lg border border-slate-100 px-3 py-2 text-sm text-slate-400">Edit</span>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white px-5 py-12 text-center"><p class="font-semibold">Belum ada riwayat pengaduan.</p><p class="mt-2 text-sm text-slate-500">Pengaduan yang Anda kirim akan tercatat di halaman ini.</p></div>
        @endforelse
    </section>

    <div class="mt-5">{{ $laporans->links() }}</div>
@endsection