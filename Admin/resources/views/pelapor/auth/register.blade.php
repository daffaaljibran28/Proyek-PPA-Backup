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
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            pink: '#FF6B95',
                            header: '#FA5C83',
                            button: '#D82357',
                            buttonHover: '#B81A48',
                            text: '#BE2654',
                            border: '#F4B0C4',
                            bg: '#FFF1F4'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .header-wave {
            background: linear-gradient(135deg, #FF6EA7 0%, #F44376 100%);
        }
    </style>
</head>
<body class="min-h-screen bg-brand-bg text-slate-800 flex items-center justify-center p-4 sm:p-6">

    <!-- Container Card Lebar & Proporsional -->
    <main class="w-full max-w-[580px] my-auto">
        <div class="overflow-hidden rounded-[36px] bg-white shadow-2xl shadow-rose-200/50 flex flex-col relative">
            
            <!-- ================= HEADER WAVE BERGELOMBANG ================= -->
            <section class="header-wave relative pt-12 pb-20 px-6 text-white text-center rounded-t-[36px]">
                <div class="absolute -bottom-[1px] left-0 w-full overflow-hidden leading-none">
                    <svg class="relative block w-full h-16 text-white transform scale-y-105" viewBox="0 0 1440 320" preserveAspectRatio="none" fill="currentColor">
                        <path d="M0,160L48,176C96,192,192,224,288,213.3C384,203,480,149,576,138.7C672,128,768,160,864,186.7C960,213,1056,235,1152,218.7C1248,203,1344,149,1392,122.7L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                    </svg>
                </div>

                <!-- Logo Hati Berjabat Tangan Mengambang di Tengah -->
                <div class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 z-20">
                    <div class="w-24 h-24 bg-white rounded-full shadow-lg shadow-rose-200/60 flex items-center justify-center border-4 border-white p-3">
                        <svg class="w-full h-full text-brand-button" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                            <path d="M12 5 9.04 7.96a2.17 2.17 0 0 0 0 3.08v0c.82.82 2.13.85 3 .07l2.07-1.9"/>
                            <path d="m14 10 1.5 1.5"/>
                        </svg>
                    </div>
                </div>
            </section>

            <!-- ================= ISIAN FORM REGISTER ================= -->
            <section class="pt-14 pb-10 px-8 sm:px-14">
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold text-brand-text tracking-tight mb-1">Buat Akun Pelapor</h1>
                    <p class="text-sm text-slate-500">Data digunakan untuk mengelola akun dan laporan Anda.</p>
                </div>

                @if ($errors->any())
                    <div role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-medium text-rose-700">
                        Periksa kembali data yang Anda masukkan.
                    </div>
                @endif

                <form method="POST" action="{{ route('pelapor.register.post') }}" class="grid gap-4 sm:grid-cols-2">
                    @csrf
                    
                    <!-- Nama Lengkap -->
                    <div>
                        <label for="name" class="mb-1.5 block text-sm font-semibold text-brand-text">Nama lengkap</label>
                        <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" required 
                            placeholder="Nama lengkap Anda"
                            class="w-full rounded-xl border border-brand-border bg-white px-4 py-3 text-sm text-slate-700 outline-none focus:border-brand-header focus:ring-2 focus:ring-rose-100 transition-all placeholder-slate-400">
                        @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- NIK -->
                    <div>
                        <label for="nik" class="mb-1.5 block text-sm font-semibold text-brand-text">NIK</label>
                        <input id="nik" name="nik" inputmode="numeric" maxlength="16" value="{{ old('nik') }}" required 
                            placeholder="Nomor Induk Kependudukan"
                            class="w-full rounded-xl border border-brand-border bg-white px-4 py-3 text-sm text-slate-700 outline-none focus:border-brand-header focus:ring-2 focus:ring-rose-100 transition-all placeholder-slate-400">
                        @error('nik')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Tanggal Lahir -->
                    <div>
                        <label for="tanggal_lahir" class="mb-1.5 block text-sm font-semibold text-brand-text">Tanggal lahir</label>
                        <input id="tanggal_lahir" name="tanggal_lahir" type="date" max="{{ now()->toDateString() }}" value="{{ old('tanggal_lahir') }}" required 
                            class="w-full rounded-xl border border-brand-border bg-white px-4 py-3 text-sm text-slate-700 outline-none focus:border-brand-header focus:ring-2 focus:ring-rose-100 transition-all">
                        @error('tanggal_lahir')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Nomor HP -->
                    <div>
                        <label for="no_hp" class="mb-1.5 block text-sm font-semibold text-brand-text">Nomor HP</label>
                        <input id="no_hp" name="no_hp" type="tel" inputmode="numeric" value="{{ old('no_hp') }}" autocomplete="tel" required 
                            placeholder="08xxxxxxxxxx"
                            class="w-full rounded-xl border border-brand-border bg-white px-4 py-3 text-sm text-slate-700 outline-none focus:border-brand-header focus:ring-2 focus:ring-rose-100 transition-all placeholder-slate-400">
                        @error('no_hp')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Alamat -->
                    <div class="sm:col-span-2">
                        <label for="alamat" class="mb-1.5 block text-sm font-semibold text-brand-text">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="2" required 
                            placeholder="Alamat lengkap tempat tinggal"
                            class="w-full rounded-xl border border-brand-border bg-white px-4 py-3 text-sm text-slate-700 outline-none focus:border-brand-header focus:ring-2 focus:ring-rose-100 transition-all placeholder-slate-400">{{ old('alamat') }}</textarea>
                        @error('alamat')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Email -->
                    <div class="sm:col-span-2">
                        <label for="email" class="mb-1.5 block text-sm font-semibold text-brand-text">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required 
                            placeholder="contoh@email.com"
                            class="w-full rounded-xl border border-brand-border bg-white px-4 py-3 text-sm text-slate-700 outline-none focus:border-brand-header focus:ring-2 focus:ring-rose-100 transition-all placeholder-slate-400">
                        @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Kata Sandi dengan Tombol Lihat/Sembunyikan -->
                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-semibold text-brand-text">Kata sandi</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required 
                                placeholder="Minimal 8 karakter"
                                class="w-full rounded-xl border border-brand-border bg-white px-4 py-3 pr-12 text-sm text-slate-700 outline-none focus:border-brand-header focus:ring-2 focus:ring-rose-100 transition-all placeholder-slate-400">
                            <button type="button" onclick="togglePassword('password', 'icon-password')" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                <svg id="icon-password" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Ulangi Kata Sandi dengan Tombol Lihat/Sembunyikan -->
                    <div>
                        <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-brand-text">Ulangi kata sandi</label>
                        <div class="relative">
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required 
                                placeholder="Ulangi kata sandi"
                                class="w-full rounded-xl border border-brand-border bg-white px-4 py-3 pr-12 text-sm text-slate-700 outline-none focus:border-brand-header focus:ring-2 focus:ring-rose-100 transition-all placeholder-slate-400">
                            <button type="button" onclick="togglePassword('password_confirmation', 'icon-password-confirm')" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                <svg id="icon-password-confirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Daftar -->
                    <div class="sm:col-span-2 pt-2">
                        <button type="submit" class="w-full rounded-xl bg-brand-button px-4 py-3.5 text-sm font-semibold text-white hover:bg-brand-buttonHover shadow-md transition-all active:scale-[0.99]">
                            Daftar
                        </button>
                    </div>
                </form>

                <!-- Tautan ke Halaman Login -->
                <p class="mt-6 text-center text-sm text-slate-600">
                    Sudah memiliki akun? <a href="{{ route('pelapor.login') }}" class="font-semibold text-brand-text hover:underline">Masuk</a>
                </p>
            </section>
        </div>
    </main>

    <!-- Script JavaScript untuk Toggle Password -->
    <script>
    function togglePassword(fieldId, iconId) {
        const input = document.getElementById(fieldId);
        const icon = document.getElementById(iconId);
        
        if (input.type === 'password') {
            input.type = 'text';
            // Ikon mata dicoret (sembunyikan)
            icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.05 10.05 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.027 10.027 0 01-4.132 5.411m0 0L21 21" />`;
        } else {
            input.type = 'password';
            // Ikon mata normal (tampilkan)
            icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
        }
    }
    </script>
</body>
</html>