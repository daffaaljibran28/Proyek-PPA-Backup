@extends('pelapor.layout')

@section('title', 'Profil')

@section('content')
    <p class="text-sm text-slate-500">Akun</p>
    <h1 class="mt-1 text-2xl font-bold">Profil Pengguna</h1>
        <p class="mt-2 text-sm text-slate-600">Perbarui data kontak dan informasi akun Anda.</p>
        @if ($errors->any())
            <div role="alert" class="mt-5 max-w-3xl rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('pelapor.profile.update') }}" class="mt-6 max-w-3xl rounded-xl border border-slate-200 bg-white p-5 sm:p-7">
            @csrf
            @method('PUT')
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-medium">Nama Lengkap</label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
                    @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="no_hp" class="mb-1.5 block text-sm font-medium">No. HP</label>
                    <input id="no_hp" name="no_hp" type="tel" inputmode="numeric" value="{{ old('no_hp', $user->no_hp) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
                    @error('no_hp')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="tanggal_lahir" class="mb-1.5 block text-sm font-medium">Tanggal Lahir</label>
                    <input id="tanggal_lahir" name="tanggal_lahir" type="date" max="{{ now()->subDay()->toDateString() }}" value="{{ old('tanggal_lahir', $tanggalLahir) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
                    @error('tanggal_lahir')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="nik" class="mb-1.5 block text-sm font-medium">NIK</label>
                    <input id="nik" value="{{ $user->nik }}" readonly aria-readonly="true" class="w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-500">
                    <p class="mt-1 text-xs text-slate-500">NIK tidak dapat diubah.</p>
                </div>
                <div class="sm:col-span-2">
                    <label for="alamat" class="mb-1.5 block text-sm font-medium">Alamat</label>
                    <textarea id="alamat" name="alamat" rows="3" maxlength="2000" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">{{ old('alamat', $user->alamat) }}</textarea>
                    @error('alamat')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-7 border-t border-slate-100 pt-5">
                <h2 class="font-semibold">Ubah Kata Sandi</h2>
                <p class="mt-1 text-sm text-slate-500">Kosongkan jika tidak ingin mengubah kata sandi.</p>
                <div class="mt-4 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="password_sekarang" class="mb-1.5 block text-sm font-medium">Kata Sandi Saat Ini</label>
                        <input id="password_sekarang" name="password_sekarang" type="password" autocomplete="current-password" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
                        @error('password_sekarang')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password_baru" class="mb-1.5 block text-sm font-medium">Password Baru</label>
                        <input id="password_baru" name="password_baru" type="password" autocomplete="new-password" minlength="8" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
                        @error('password_baru')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password_baru_confirmation" class="mb-1.5 block text-sm font-medium">Konfirmasi Password Baru</label>
                        <input id="password_baru_confirmation" name="password_baru_confirmation" type="password" autocomplete="new-password" minlength="8" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end border-t border-slate-100 pt-5"><button type="submit" class="rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">Simpan Profil</button></div>
        </form>
@endsection
