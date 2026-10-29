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

        <!-- Daftar Informasi / Artikel (Card Berita 2 Kolom) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-start">
            @forelse($informasis as $item)
            <article class="group flex flex-col overflow-hidden rounded-3xl border border-pink-100/70 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                <!-- Gambar / Thumbnail Berita -->
                <div class="relative h-44 w-full overflow-hidden bg-gradient-to-br from-rose-100 to-pink-50">
                    @if($item->gambar)
                        <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->judul }}"
                             class="h-full w-full object-cover object-center transition duration-300 group-hover:scale-105">
                    @else
                        <div class="flex h-full w-full items-center justify-center">
                            <svg class="h-12 w-12 text-rose-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                            </svg>
                        </div>
                    @endif

                    <!-- Badge Tanggal Publish -->
                    <span class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-white/95 px-3 py-1 text-[11px] font-semibold text-rose-600 shadow-sm backdrop-blur">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                        </svg>
                        {{ $item->tanggal_publish ? \Carbon\Carbon::parse($item->tanggal_publish)->translatedFormat('d M Y') : '-' }}
                    </span>
                </div>

                <!-- Isi Kartu -->
                <div class="flex flex-1 flex-col gap-2 p-5">
                    <h3 class="text-sm font-bold leading-snug text-gray-900 line-clamp-2">{{ $item->judul }}</h3>

                    <!-- Cuplikan Konten (disembunyikan otomatis saat "Baca Selengkapnya" dibuka) -->
                    <p class="clamp-preview text-xs text-slate-600 leading-relaxed whitespace-pre-line break-words line-clamp-3">
                        {{ $item->konten }}
                    </p>

                    <!-- Baca Selengkapnya (buka/tutup isi lengkap) -->
                    <details class="mt-auto">
                        <summary class="mt-2 inline-flex cursor-pointer list-none items-center gap-1 text-xs font-bold text-rose-600 hover:text-rose-700">
                            Baca Selengkapnya
                            <svg class="chevron h-3.5 w-3.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </summary>
                        <div class="mt-3 border-t border-slate-100 pt-3 text-xs text-slate-600 leading-relaxed whitespace-pre-line break-words">
                            {{ $item->konten }}
                        </div>
                    </details>
                </div>
            </article>
            @empty
            <!-- Kosong -->
            <div class="md:col-span-2 bg-white p-10 rounded-3xl border border-pink-100/70 text-center space-y-2">
                <div class="text-3xl">📭</div>
                <p class="text-xs font-bold text-slate-700">Belum ada informasi atau edukasi.</p>
                <p class="text-[11px] text-slate-400">Informasi terbaru akan segera dibagikan oleh admin.</p>
            </div>
            @endforelse
        </div>
    </div>

    <style>
        /* Ikon panah "Baca Selengkapnya" berputar saat isi artikel dibuka */
        details[open] > summary .chevron { transform: rotate(180deg); }
        /* Cuplikan pendek disembunyikan agar tidak dobel dengan isi lengkap */
        .clamp-preview:has(+ details[open]) { display: none; }
    </style>
@endsection
