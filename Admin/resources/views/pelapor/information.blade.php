@extends('pelapor.layout')

@section('title', 'Informasi & Edukasi')

@section('content')
    <div class="space-y-6 max-w-5xl mx-auto">
        <!-- Header Halaman Pelapor -->
        <div>
            <p class="text-xs font-medium text-slate-400">Edukasi</p>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Informasi & Edukasi</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Pusat informasi, artikel, dan edukasi seputar perlindungan dan pencegahan kekerasan.</p>
        </div>

        <!-- Daftar Informasi / Artikel -->
        <div class="grid grid-cols-1 gap-4">
            @forelse($informasis as $item)
            <div class="bg-white p-6 rounded-3xl border border-pink-100/70 shadow-sm hover:shadow-md transition space-y-4">
                <!-- Judul & Tanggal -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <h3 class="text-sm font-bold text-gray-900">{{ $item->judul }}</h3>
                    <span class="text-[11px] font-medium text-slate-400 bg-slate-50 px-3 py-1 rounded-full w-fit">
                        {{ $item->tanggal_publish ? \Carbon\Carbon::parse($item->tanggal_publish)->format('d M Y') : $item->created_at->format('d M Y') }}
                    </span>
                </div>

                <!-- Gambar Lampiran (Diperbaiki agar proporsional dan rapi) -->
                @if($item->gambar)
                <div class="w-full h-64 sm:h-72 overflow-hidden rounded-2xl border border-slate-100 bg-slate-50 flex items-center justify-center">
                    <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->judul }}" class="w-full h-full object-cover object-center">
                </div>
                @endif

                <!-- Isi Konten / Deskripsi -->
                <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line">
                    {{ $item->konten }}
                </p>
            </div>
            @empty
            <!-- Kosong -->
            <div class="bg-white p-10 rounded-3xl border border-pink-100/70 text-center space-y-2">
                <div class="text-3xl">📭</div>
                <p class="text-xs font-bold text-slate-700">Belum ada informasi atau edukasi.</p>
                <p class="text-[11px] text-slate-400">Informasi terbaru akan segera dibagikan oleh admin.</p>
            </div>
            @endforelse
        </div>
    </div>
@endsection