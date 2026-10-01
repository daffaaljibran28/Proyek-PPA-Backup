@extends('pelapor.layout')

@section('title', 'Emergency Call')

@section('content')
    <a href="{{ route('pelapor.home') }}" class="text-sm font-medium text-rose-700 hover:underline">← Beranda</a>
    <section class="mx-auto mt-6 max-w-2xl rounded-xl border border-rose-200 bg-white p-6 sm:p-9">
        <p class="text-sm font-semibold text-rose-700">Emergency Call</p>
        <h1 class="mt-2 text-2xl font-bold">Hubungi UPTD PPA</h1>
        <p class="mt-3 text-sm leading-6 text-slate-600">Untuk bantuan darurat, hubungi UPTD PPA melalui WhatsApp. Tautan ini membuka WhatsApp pada perangkat Anda.</p>
        <a href="https://wa.me/6281138808800" target="_blank" rel="noopener noreferrer" class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-5 py-3 font-semibold text-white hover:bg-emerald-700 sm:w-auto">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.5 3.5A11.8 11.8 0 0 0 12.1 0C5.6 0 .3 5.3.3 11.8c0 2.1.6 4.1 1.6 5.9L0 24l6.5-1.7a11.8 11.8 0 0 0 5.6 1.4h.1c6.5 0 11.8-5.3 11.8-11.8 0-3.2-1.2-6.1-3.5-8.4ZM12.1 21.7c-1.8 0-3.5-.5-5-1.4l-.4-.2-3.9 1 1-3.8-.2-.4a9.8 9.8 0 1 1 8.5 4.8Zm5.4-7.3c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.1-.7.2-.2.3-.8.9-1 1.1-.2.2-.4.2-.7.1-.3-.1-1.2-.4-2.3-1.4-.8-.7-1.4-1.7-1.6-2-.2-.3 0-.5.1-.6l.5-.6c.1-.2.2-.3.3-.5.1-.2 0-.4 0-.5s-.7-1.6-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4s-1 1-1 2.4 1 2.8 1.1 3c.1.2 2 3.1 4.8 4.3.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.6-.1 1.7-.7 1.9-1.3.2-.6.2-1.2.2-1.3-.1-.1-.3-.2-.6-.4Z"/></svg>
            Buka WhatsApp UPTD PPA
        </a>
        <p class="mt-3 text-xs text-slate-500">Nomor tujuan: 0811-3880-8800</p>
    </section>
@endsection