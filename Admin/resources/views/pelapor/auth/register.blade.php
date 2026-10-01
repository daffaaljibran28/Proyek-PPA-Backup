<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pelapor | Sahabat PPA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="min-h-screen bg-rose-50 px-4 py-10 text-slate-800">
    <main class="mx-auto max-w-2xl rounded-2xl bg-white p-6 shadow-xl shadow-rose-100 sm:p-10">
        <a href="{{ route('pelapor.login') }}" class="text-sm font-semibold text-rose-700">Sahabat PPA</a>
        <h1 class="mt-5 text-2xl font-bold">Buat akun pelapor</h1>
        <p class="mt-2 text-sm text-slate-500">Data digunakan untuk mengelola akun dan laporan Anda.</p>
        @if ($errors->any())
            <div role="alert" class="mt-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">Periksa kembali data yang Anda masukkan.</div>
        @endif
        <form method="POST" action="{{ route('pelapor.register.post') }}" class="mt-7 grid gap-5 sm:grid-cols-2">
            @csrf
            <div>
                <label for="name" class="mb-1.5 block text-sm font-medium">Nama lengkap</label>
                <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
                @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="nik" class="mb-1.5 block text-sm font-medium">NIK</label>
                <input id="nik" name="nik" inputmode="numeric" maxlength="16" value="{{ old('nik') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
                @error('nik')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="tanggal_lahir" class="mb-1.5 block text-sm font-medium">Tanggal lahir</label>
                <input id="tanggal_lahir" name="tanggal_lahir" type="date" max="{{ now()->toDateString() }}" value="{{ old('tanggal_lahir') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
                @error('tanggal_lahir')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="no_hp" class="mb-1.5 block text-sm font-medium">Nomor HP</label>
                <input id="no_hp" name="no_hp" type="tel" inputmode="numeric" value="{{ old('no_hp') }}" autocomplete="tel" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
                @error('no_hp')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="alamat" class="mb-1.5 block text-sm font-medium">Alamat</label>
                <textarea id="alamat" name="alamat" rows="2" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">{{ old('alamat') }}</textarea>
                @error('alamat')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="email" class="mb-1.5 block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
                @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium">Kata sandi</label>
                <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
                @error('password')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-medium">Ulangi kata sandi</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
            </div>
            <button type="submit" class="sm:col-span-2 rounded-lg bg-rose-600 px-4 py-3 text-sm font-semibold text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">Daftar</button>
        </form>
        <p class="mt-6 text-center text-sm text-slate-600">Sudah memiliki akun? <a href="{{ route('pelapor.login') }}" class="font-semibold text-rose-700 hover:underline">Masuk</a></p>
    </main>
</body>
</html>