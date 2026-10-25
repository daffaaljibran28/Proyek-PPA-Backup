@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Tambah Informasi & Edukasi</h1>
        <a href="{{ route('admin.informasi.index') }}" class="text-sm font-medium text-slate-500 hover:text-rose-600 transition">Kembali</a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8">
        <!-- Tambahkan enctype="multipart/form-data" agar form bisa mengirim file foto -->
        <form action="{{ route('admin.informasi.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label for="judul" class="block text-sm font-semibold text-slate-700 mb-1.5">Judul Informasi</label>
                <input type="text" id="judul" name="judul" value="{{ old('judul') }}" required placeholder="Masukkan judul informasi..."
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-100 transition">
                @error('judul')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700 mb-1.5">Status Publikasi</label>
                    <select id="status" name="status" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-100 transition">
                        <option value="draf">Draf (Simpan Saja)</option>
                        <option value="published">Published (Tampilkan ke Publik)</option>
                    </select>
                </div>

                <div>
                    <label for="tanggal_publish" class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Publish</label>
                    <input type="date" id="tanggal_publish" name="tanggal_publish" value="{{ old('tanggal_publish', date('Y-m-d')) }}" required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-100 transition">
                </div>
            </div>

            <div>
                <label for="konten" class="block text-sm font-semibold text-slate-700 mb-1.5">Isi Konten / Deskripsi</label>
                <textarea id="konten" name="konten" rows="6" required placeholder="Tuliskan isi informasi atau edukasi di sini..."
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-100 transition">{{ old('konten') }}</textarea>
                @error('konten')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <!-- Input Unggah Gambar ala Threads -->
            <div>
                <label for="gambar" class="block text-sm font-semibold text-slate-700 mb-1.5">Lampiran Gambar / Foto (Opsional)</label>
                <input type="file" id="gambar" name="gambar" accept="image/*"
                    class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100 transition border border-slate-200 rounded-xl cursor-pointer">
                <p class="mt-1 text-xs text-slate-400">Format yang didukung: JPG, JPEG, PNG (Maksimal 5MB)</p>
                @error('gambar')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <a href="{{ route('admin.informasi.index') }}" class="px-5 py-3 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Batal</a>
                <button type="submit" class="px-5 py-3 rounded-xl bg-rose-600 text-sm font-semibold text-white shadow-md shadow-rose-200 hover:bg-rose-700 transition">Simpan Informasi</button>
            </div>
        </form>
    </div>
</div>
@endsection