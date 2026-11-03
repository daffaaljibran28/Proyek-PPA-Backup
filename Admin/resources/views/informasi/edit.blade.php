@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Edit Informasi & Edukasi</h1>
        <a href="{{ route('admin.informasi.index') }}" class="text-sm font-medium text-slate-500 hover:text-rose-600 transition">Kembali</a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8">
        <form action="{{ route('admin.informasi.update', $informasi->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="judul" class="block text-sm font-semibold text-slate-700 mb-1.5">Judul Informasi</label>
                <input type="text" id="judul" name="judul" value="{{ old('judul', $informasi->judul) }}" required placeholder="Masukkan judul informasi..."
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-100 transition">
                @error('judul')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700 mb-1.5">Status Publikasi</label>
                    <select id="status" name="status" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-100 transition">
                        <option value="draf" {{ old('status', $informasi->status) == 'draf' ? 'selected' : '' }}>Draf (Simpan Saja)</option>
                        <option value="published" {{ old('status', $informasi->status) == 'published' ? 'selected' : '' }}>Published (Tampilkan ke Publik)</option>
                    </select>
                </div>

                <div>
                    <label for="tanggal_publish" class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Publish</label>
                    <input type="date" id="tanggal_publish" name="tanggal_publish" value="{{ old('tanggal_publish', $informasi->tanggal_publish) }}" required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-100 transition">
                </div>
            </div>

            <div>
                <label for="konten" class="block text-sm font-semibold text-slate-700 mb-1.5">Isi Konten / Deskripsi</label>
                <textarea id="konten" name="konten" rows="6" required placeholder="Tuliskan isi informasi atau edukasi di sini..."
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-100 transition">{{ old('konten', $informasi->konten) }}</textarea>
                @error('konten')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:p-5">
                <div class="mb-3 flex items-start gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-rose-100 text-rose-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 15-5-5L5 21"/></svg>
                    </span>
                    <div>
                        <label for="gambar" class="block text-sm font-semibold text-slate-800">Lampiran Gambar / Foto <span class="font-normal text-slate-400">(Opsional)</span></label>
                        <p class="mt-1 text-xs leading-5 text-slate-500">Unggah gambar baru jika ingin mengganti lampiran saat ini.</p>
                    </div>
                </div>
                @if($informasi->gambar)
                    <div class="mb-3 flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3">
                        <img src="{{ asset('storage/' . $informasi->gambar) }}" alt="Pratinjau gambar saat ini" class="h-16 w-16 rounded-lg border border-slate-100 object-cover">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-slate-700">Gambar saat ini</p>
                            <p class="mt-1 text-xs text-slate-500">Pilih file baru di bawah untuk menggantinya.</p>
                        </div>
                    </div>
                @endif
                <input type="file" id="gambar" name="gambar" accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                    class="block w-full cursor-pointer rounded-xl border border-slate-200 bg-white text-sm text-slate-500 shadow-sm transition file:mr-4 file:cursor-pointer file:border-0 file:bg-rose-50 file:px-4 file:py-3 file:text-sm file:font-semibold file:text-rose-700 hover:border-rose-200 hover:file:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-100">
                <p class="mt-2 text-xs text-slate-500">JPG, JPEG, atau PNG · Maksimal 5 MB</p>
                @error('gambar')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                @if($informasi->gambar)
                    <label class="mt-3 inline-flex cursor-pointer items-center gap-2 rounded-lg border border-rose-100 bg-white px-3 py-2 text-xs font-medium text-slate-600 transition hover:border-rose-200 hover:bg-rose-50">
                        <input type="checkbox" name="hapus_gambar" value="1" {{ old('hapus_gambar') ? 'checked' : '' }} class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                        Hapus gambar saat ini
                    </label>
                @endif
            </div>

            <section class="overflow-hidden rounded-2xl border border-rose-100 bg-white shadow-sm">
                <div class="flex items-start gap-3 border-b border-rose-100 bg-gradient-to-r from-rose-50 to-white px-4 py-4 sm:px-5">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-rose-600 shadow-sm ring-1 ring-rose-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="3"/><path stroke-linecap="round" stroke-linejoin="round" d="m10 9 5 3-5 3V9Z"/></svg>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Video Edukasi <span class="font-normal text-slate-400">(Opsional)</span></h2>
                        <p class="mt-1 text-xs leading-5 text-slate-500">Ganti video saat ini dengan tautan maupun file video.</p>
                    </div>
                </div>
                <div class="space-y-4 p-4 sm:p-5">
                @if($informasi->video_path)
                    <div class="max-w-xl overflow-hidden rounded-xl border border-slate-200 bg-black shadow-sm">
                        <video controls playsinline preload="metadata" class="aspect-video w-full object-contain">
                            <source src="{{ url('/storage/' . $informasi->video_path) }}" type="{{ str_ends_with(strtolower($informasi->video_path), '.webm') ? 'video/webm' : 'video/mp4' }}">
                            Browser Anda tidak mendukung pemutaran video.
                        </video>
                    </div>
                @elseif($embedUrl = \App\Support\InformationVideo::embedUrl($informasi->video_url))
                    <div class="aspect-video max-w-xl overflow-hidden rounded-xl border border-slate-200 bg-black shadow-sm">
                        <iframe src="{{ $embedUrl }}" title="Video saat ini" class="h-full w-full" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                    </div>
                @endif
                @if($informasi->video_path || $informasi->video_url)
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-rose-100 bg-rose-50/60 px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-rose-50">
                        <input type="checkbox" name="hapus_video" value="1" {{ old('hapus_video') ? 'checked' : '' }} class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                        Hapus video saat ini
                    </label>
                @endif
                <div>
                    <label for="video_url" class="mb-1.5 block text-sm font-semibold text-slate-700">Tautan YouTube / Vimeo</label>
                    <input type="url" id="video_url" name="video_url" value="{{ old('video_url', $informasi->video_url) }}" placeholder="https://www.youtube.com/watch?v=..."
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 hover:border-rose-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-100">
                    @error('video_url')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center gap-3" aria-hidden="true">
                    <span class="h-px flex-1 bg-slate-200"></span>
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-300"></span>
                    <span class="h-px flex-1 bg-slate-200"></span>
                </div>
                <div>
                    <label for="video" class="mb-1.5 block text-sm font-semibold text-slate-700">Ganti dengan File Video</label>
                    <input type="file" id="video" name="video" accept=".mp4,.webm,video/mp4,video/webm"
                        class="block w-full cursor-pointer rounded-xl border border-slate-200 bg-white text-sm text-slate-500 shadow-sm transition file:mr-4 file:cursor-pointer file:border-0 file:bg-rose-50 file:px-4 file:py-3 file:text-sm file:font-semibold file:text-rose-700 hover:border-rose-200 hover:file:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-100">
                    <p class="mt-2 text-xs leading-5 text-slate-500">MP4 atau WebM · Maksimal 50 MB. Tautan dan file tidak dapat digunakan bersamaan.</p>
                    @error('video')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
                </div>
            </section>

            <div class="pt-4 flex justify-end gap-3">
                <a href="{{ route('admin.informasi.index') }}" class="px-5 py-3 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Batal</a>
                <button type="submit" class="px-5 py-3 rounded-xl bg-rose-600 text-sm font-semibold text-white shadow-md shadow-rose-200 hover:bg-rose-700 transition">Perbarui Informasi</button>
            </div>
        </form>
    </div>
</div>
@endsection