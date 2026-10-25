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
        <div class="overflow-hidden rounded-[36px] bg-white shadow-2xl shadow-rose-200/50 border border-rose-100 flex flex-col relative pb-4">
            
            <!-- ================= HEADER WAVE BERGELOMBANG FIGMA ================= -->
            <!-- overflow-hidden dihapus dari section ini agar lingkaran bawah tidak terpotong -->
            <section class="header-wave relative pt-12 pb-20 px-6 text-white text-center rounded-t-[36px]">
                <!-- SVG Gelombang Asimetris Persis Figma -->
                <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none">
                    <svg class="relative block w-full h-16 text-white" viewBox="0 0 1440 320" preserveAspectRatio="none" fill="currentColor">
                        <path d="M0,160L48,176C96,192,192,224,288,213.3C384,203,480,149,576,138.7C672,128,768,160,864,186.7C960,213,1056,235,1152,218.7C1248,203,1344,149,1392,122.7L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                    </svg>
                </div>

                <!-- Logo Hati Berjabat Tangan Mengambang di Tengah -->
                <div class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 z-20">
                    <div class="w-24 h-24 bg-white rounded-full shadow-lg shadow-rose-200/60 flex items-center justify-center border-4 border-white p-3">
                        <!-- Icon SVG Hati Berjabat Tangan -->
                        <svg class="w-full h-full text-brand-button" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                            <path d="M12 5 9.04 7.96a2.17 2.17 0 0 0 0 3.08v0c.82.82 2.13.85 3 .07l2.07-1.9"/>
                            <path d="m14 10 1.5 1.5"/>
                        </svg>
                    </div>
                </div>
            </section>

            <!-- ================= ISIAN FORM LOGIN ================= -->
            <section class="pt-14 pb-8 px-8 sm:px-14">
                <!-- Judul & Subjudul -->
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold text-brand-text tracking-tight mb-1">Masuk ke Akun Anda</h1>
                    <p class="text-sm text-slate-500">Gunakan email dan kata sandi akun pelapor.</p>
                </div>

                <!-- Session Flash Messages -->
                @if (session('success'))
                    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-800 text-center">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-medium text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <!-- Form Login -->
                <form method="POST" action="{{ route('pelapor.login.post') }}" class="space-y-4">
                    @csrf
                    
                    <!-- Input Email / No HP -->
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-semibold text-brand-text">Email atau Nomor HP</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3.5 text-brand-text">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <input id="email" name="email" type="text" value="{{ old('email') }}" autocomplete="email" required 
                                placeholder="contoh@email.com"
                                class="w-full rounded-xl border border-brand-border bg-white pl-11 pr-4 py-3 text-sm text-slate-700 outline-none focus:border-brand-header focus:ring-2 focus:ring-rose-100 transition-all placeholder-slate-400">
                        </div>
                        @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Input Password dengan Toggle Eye -->
                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-semibold text-brand-text">Password</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3.5 text-brand-text">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input id="password" name="password" type="password" autocomplete="current-password" required 
                                placeholder="Masukkan password"
                                class="w-full rounded-xl border border-brand-border bg-white pl-11 pr-11 py-3 text-sm text-slate-700 outline-none focus:border-brand-header focus:ring-2 focus:ring-rose-100 transition-all placeholder-slate-400">
                            
                            <!-- Toggle Password JS -->
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3.5 text-brand-text hover:opacity-80 focus:outline-none">
                                <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Ingat Saya & Lupa Password -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 text-sm text-brand-text cursor-pointer">
                            <input name="remember" type="checkbox" value="1" class="rounded border-rose-300 text-brand-button focus:ring-rose-400 w-4 h-4"> 
                            Ingat saya
                        </label>
                        <a href="{{ route('pelapor.password.request') }}" class="text-sm font-semibold text-brand-text hover:underline">
                            Lupa Password?
                        </a>
                    </div>

                    <!-- Tombol Masuk, Garis "atau", & Tombol Daftar Akun -->
                    <div class="pt-3 space-y-3">
                        <!-- Tombol Masuk -->
                        <button type="submit" class="w-full rounded-xl bg-brand-button px-4 py-3.5 text-sm font-semibold text-white hover:bg-brand-buttonHover shadow-md transition-all active:scale-[0.99]">
                            Masuk
                        </button>

                        <!-- Pembatas "atau" -->
                        <div class="relative flex py-1 items-center">
                            <div class="flex-grow border-t border-slate-200"></div>
                            <span class="flex-shrink mx-3 text-xs text-slate-400 font-medium">atau</span>
                            <div class="flex-grow border-t border-slate-200"></div>
                        </div>

                        <!-- Tombol Daftar Akun -->
                        <a href="{{ route('pelapor.register') }}" class="w-full flex justify-center items-center rounded-xl border border-brand-button bg-white px-4 py-3.5 text-sm font-semibold text-brand-button hover:bg-rose-50 transition-all shadow-sm">
                            Daftar Akun
                        </a>
                    </div>
                </form>
            </section>
        </div>
    </main>

    <!-- Script Toggle Password -->
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.025 10.025 0 0110.14 3.937C20.268 11.057 16.478 14 12 14c-.69 0-1.366-.07-2.02-.206m-3.414-3.414a3 3 0 014.242-4.242" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3l18 18" />
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        }
    </script>
</body>
</html>