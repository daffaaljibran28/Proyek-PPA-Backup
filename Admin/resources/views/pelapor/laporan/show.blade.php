@extends('pelapor.layout')

@section('title', 'Detail Pengaduan')

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div><a href="{{ route('pelapor.laporan.index') }}" class="text-sm font-medium text-rose-700 hover:underline">← Riwayat Pengaduan</a><h1 class="mt-3 text-2xl font-bold">Detail Pengaduan</h1><p class="mt-1 text-sm text-slate-500">{{ $laporan->nomor_laporan }}</p></div>
        <span class="w-fit rounded-full px-3 py-1.5 text-sm font-semibold {{ $laporan->status_penanganan === 'Selesai' ? 'bg-emerald-50 text-emerald-700' : ($laporan->status_penanganan === 'Sedang Diproses' || $laporan->status_penanganan === 'Diproses' ? 'bg-blue-50 text-blue-700' : ($laporan->status_penanganan === 'Ditolak' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700')) }}">{{ $laporan->status_penanganan }}</span>
    </div>

    <section class="mt-6 grid min-w-0 grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,22rem)]">
        <div class="min-w-0 rounded-xl border border-slate-200 bg-white p-5 sm:p-7">
            <h2 class="font-bold">Informasi Kejadian</h2>
            <dl class="mt-5 grid gap-x-6 gap-y-5 sm:grid-cols-2">
                <div class="min-w-0"><dt class="text-xs text-slate-500">Kategori Kejadian</dt><dd class="mt-1 break-words text-sm font-semibold [overflow-wrap:anywhere]">{{ $laporan->kategori }}</dd></div>
                <div class="min-w-0"><dt class="text-xs text-slate-500">Melapor Sebagai</dt><dd class="mt-1 break-words text-sm font-semibold [overflow-wrap:anywhere]">{{ $laporan->sebagai ?: '-' }}</dd></div>
                <div class="min-w-0"><dt class="text-xs text-slate-500">Lokasi Kejadian</dt><dd class="mt-1 break-words text-sm font-semibold [overflow-wrap:anywhere]">{{ $laporan->lokasi_kejadian ?: '-' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Tanggal Kejadian</dt><dd class="mt-1 text-sm font-semibold">{{ optional($laporan->tanggal_kejadian)->format('d M Y') ?: '-' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Tanggal Pengiriman</dt><dd class="mt-1 text-sm font-semibold">{{ optional($laporan->created_at)->format('d M Y, H:i') ?: '-' }}</dd></div>
            </dl>
            <div class="mt-6 min-w-0 border-t border-slate-100 pt-5"><h3 class="text-sm font-bold">Penjelasan Kejadian</h3><p class="mt-2 min-w-0 whitespace-pre-wrap break-words rounded-lg bg-slate-50 p-4 text-sm leading-6 text-slate-700 [overflow-wrap:anywhere]">{{ $laporan->kronologi ?: '-' }}</p></div>
            @if ($laporan->bukti_lampiran)
                <div class="mt-5"><h3 class="text-sm font-bold">Bukti Foto</h3><div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($buktiFoto as $index => $path)
                        @if (str_starts_with($path, 'laporan/' . $laporan->id_laporan . '/'))<a href="{{ route('pelapor.laporan.evidence', [$laporan->id_laporan, $index]) }}" target="_blank" rel="noopener"><img src="{{ route('pelapor.laporan.evidence', [$laporan->id_laporan, $index]) }}" alt="Bukti foto pengaduan" class="aspect-square w-full rounded-lg border border-slate-200 object-cover"></a>@else<p class="break-all text-sm text-slate-600">{{ $path }}</p>@endif
                    @endforeach
                </div></div>
            @endif
        </div>
        <aside class="min-w-0 w-full rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
            <h2 class="font-bold">Status Penanganan</h2>
            <ol class="mt-5 space-y-4 text-sm">
                @forelse ($laporan->riwayat_status ?? [] as $entry)
                    <li class="flex min-w-0 gap-3"><span class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-full bg-rose-100 text-xs font-bold text-rose-700">{{ $loop->iteration }}</span><span class="min-w-0 break-words [overflow-wrap:anywhere]"><strong class="block">{{ $entry['status'] ?? 'Pembaruan' }}</strong><time class="mt-0.5 block text-xs text-slate-500">{{ !empty($entry['pada']) ? \Illuminate\Support\Carbon::parse($entry['pada'])->format('d M Y, H:i') : '' }}{{ !empty($entry['oleh']) ? ' · ' . $entry['oleh'] : '' }}</time>@if (!empty($entry['pesan']))<span class="mt-2 block break-words rounded-lg bg-rose-50 p-3 text-sm leading-5 text-slate-700 [overflow-wrap:anywhere]">{{ $entry['pesan'] }}</span>@endif</span></li>
                @empty
                    <li class="text-sm text-slate-600">Status terkini: {{ $laporan->status_penanganan }}</li>
                @endforelse
            </ol>
            @if ($laporan->pesan_pelapor && !collect($laporan->riwayat_status ?? [])->contains(fn ($entry) => ($entry['pesan'] ?? null) === $laporan->pesan_pelapor))
                <div class="mt-4 rounded-lg bg-rose-50 p-3 text-sm text-slate-700"><p class="font-semibold">Pesan dari petugas</p><p class="mt-1">{{ $laporan->pesan_pelapor }}</p></div>
            @endif
            @if ($laporan->status_penanganan === 'Menunggu Verifikasi')
                <a href="{{ route('pelapor.laporan.edit', $laporan->id_laporan) }}" class="mt-5 block rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold hover:bg-slate-50">Edit Pengaduan</a>
            @else
                <p class="mt-5 border-t border-slate-100 pt-4 text-xs leading-5 text-slate-500">Pengaduan hanya dapat diedit saat Menunggu Verifikasi.</p>
            @endif
        </aside>
    </section>
@endsection