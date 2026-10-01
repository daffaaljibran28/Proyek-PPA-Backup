<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Pelapor | Sahabat PPA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="min-h-screen bg-rose-50 text-slate-800">
    <main class="mx-auto flex min-h-screen max-w-6xl items-center px-5 py-10">
        <div class="grid w-full overflow-hidden rounded-2xl bg-white shadow-xl shadow-rose-100 md:grid-cols-2">
            <section class="bg-rose-600 px-8 py-12 text-white md:flex md:flex-col md:justify-between md:px-12">
                <a href="{{ route('pelapor.login') }}" class="text-xl font-extrabold">Sahabat PPA</a>
                <div class="my-12">
                    <p class="mb-3 text-sm font-semibold uppercase text-rose-100">Ruang Pelapor</p>
                    <h1 class="max-w-md text-3xl font-bold leading-tight">Akses layanan dan pantau laporan Anda.</h1>
                    <p class="mt-4 max-w-md text-sm leading-6 text-rose-100">Masuk untuk membuat pengaduan dan melihat perkembangannya secara pribadi.</p>
                </div>
                <p class="text-sm text-rose-100">SAPA · Sistem Pelaporan Aman</p>
            </section>
            <section class="px-6 py-10 sm:px-12 sm:py-14">
                <h2 class="text-2xl font-bold">Masuk ke akun</h2>
                <p class="mt-2 text-sm text-slate-500">Gunakan email dan kata sandi akun pelapor.</p>

                @if (session('success'))
                    <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div role="alert" class="mt-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('pelapor.login.post') }}" class="mt-7 space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-100">
                        @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium">Kata sandi</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-100">
                        @error('password')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-600"><input name="remember" type="checkbox" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500"> Ingat saya</label>
                    <button type="submit" class="w-full rounded-lg bg-rose-600 px-4 py-3 text-sm font-semibold text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">Masuk</button>
                </form>
                <p class="mt-6 text-center text-sm text-slate-600">Belum memiliki akun? <a href="{{ route('pelapor.register') }}" class="font-semibold text-rose-700 hover:underline">Daftar</a></p>
            </section>
        </div>
    </main>
</body>
</html>