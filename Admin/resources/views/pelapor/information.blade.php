@extends('pelapor.layout')

@section('title', 'Informasi/Edukasi')

@section('content')
    <p class="text-sm text-slate-500">Sumber informasi</p>
    <h1 class="mt-1 text-2xl font-bold">Informasi/Edukasi</h1>
    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Informasi dasar mengenai perlindungan, pelaporan, dan akses layanan bagi perempuan dan anak.</p>

    <section class="mt-6 grid gap-4 md:grid-cols-2">
        @foreach ($edukasi as $item)
            <details class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6" {{ $loop->first ? 'open' : '' }}>
                <summary class="cursor-pointer list-none"><span class="text-xs font-semibold text-rose-700">{{ $item['category'] }}</span><h2 class="mt-2 text-lg font-bold">{{ $item['title'] }}</h2><p class="mt-2 text-sm leading-6 text-slate-600">{{ $item['summary'] }}</p></summary>
                <p class="mt-4 border-t border-slate-100 pt-4 text-sm leading-6 text-slate-700">{{ $item['content'] }}</p>
            </details>
        @endforeach
    </section>

    <section class="mt-8 border-t border-slate-200 pt-6">
        <h2 class="text-lg font-bold">Kontak Layanan Darurat</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <article class="rounded-xl border border-rose-200 bg-white p-5"><p class="text-sm font-semibold text-rose-700">SAPA 129</p><h3 class="mt-1 font-bold">KemenPPPA</h3><p class="mt-1 text-sm text-slate-600">Layanan bebas pulsa</p><a href="tel:129" class="mt-4 inline-flex rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white">Hubungi 129</a></article>
            <article class="rounded-xl border border-slate-200 bg-white p-5"><p class="text-sm font-semibold text-blue-700">Polisi 110</p><h3 class="mt-1 font-bold">Layanan Kepolisian</h3><p class="mt-1 text-sm text-slate-600">Hotline darurat kepolisian</p><a href="tel:110" class="mt-4 inline-flex rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Hubungi 110</a></article>
        </div>
        <p class="mt-4 text-xs leading-5 text-slate-500">Materi edukasi ini merupakan informasi umum dan bukan pengganti bantuan profesional. Konten menggunakan struktur terkelompok di controller pelapor; belum ada tabel konten khusus pada database existing.</p>
    </section>
@endsection