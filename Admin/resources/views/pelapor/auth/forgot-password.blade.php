<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Kata Sandi | Sahabat PPA</title>
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
            
            <!-- ================= HEADER WAVE BERGELOMBANG FIGMA ================= -->
            <section class="header-wave relative pt-12 pb-20 px-6 text-white text-center rounded-t-[36px]">
                <!-- SVG Gelombang Asimetris -->
                <div class="absolute bottom-0 left-0 w-full leading-none">
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

            <!-- ================= ISIAN FORM ATUR ULANG SANDI ================= -->
            <section class="pt-14 pb-10 px-8 sm:px-14">
                <!-- Judul & Subjudul -->
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold text-brand-text tracking-tight mb-1">Atur Ulang Kata Sandi</h1>
                    <p class="text-sm text-slate-500">Masukkan akun dan kata sandi baru Anda.</p>
                </div>

                <!-- Status / Error Alerts -->
                @if (session('status'))
                    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-800 text-center">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any() && !request()->isMethod('get'))
                    <div role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-medium text-rose-700">
                        Periksa kembali data yang Anda masukkan.
                    </div>
                @endif

                <!-- Form Ganti Password -->
                <form method="POST" action="{{ route('pelapor.password.update') }}" class="space-y-4">
                    @csrf
                    
                    <!-- Input Email / No HP -->
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-semibold text-brand-text">Email atau Nomor HP</label>
                        <input id="email" name="email" type="text" value="{{ old('email') }}" required 
                            placeholder="contoh@email.com atau 08xxx"
                            class="w-full rounded-xl border border-brand-border bg-white px-4 py-3 text-sm text-slate-700 outline-none focus:border-brand-header focus:ring-2 focus:ring-rose-100 transition-all placeholder-slate-400">
                        @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Kata Sandi Baru dengan Toggle Eye -->
                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-semibold text-brand-text">Kata Sandi Baru</label>
                        <div class="relative flex items-center">
                            <input id="password" name="password" type="password" required minlength="8"
                                placeholder="Minimal 8 karakter (Huruf besar, kecil, angka, simbol)"
                                class="w-full rounded-xl border border-brand-border bg-white px-4 py-3 pr-11 text-sm text-slate-700 outline-none focus:border-brand-header focus:ring-2 focus:ring-rose-100 transition-all placeholder-slate-400">
                            
                            <!-- Toggle Button 1 -->
                            <button type="button" onclick="togglePasswordVisibility('password', 'eyeIcon1')" class="absolute right-3.5 text-brand-text hover:opacity-80 focus:outline-none">
                                <svg id="eyeIcon1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Konfirmasi Kata Sandi Baru dengan Toggle Eye -->
                    <div>
                        <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-brand-text">Ulangi Kata Sandi Baru</label>
                        <div class="relative flex items-center">
                            <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8"
                                placeholder="Ulangi kata sandi baru"
                                class="w-full rounded-xl border border-brand-border bg-white px-4 py-3 pr-11 text-sm text-slate-700 outline-none focus:border-brand-header focus:ring-2 focus:ring-rose-100 transition-all placeholder-slate-400">
                            
                            <!-- Toggle Button 2 -->
                            <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'eyeIcon2')" class="absolute right-3.5 text-brand-text hover:opacity-80 focus:outline-none">
                                <svg id="eyeIcon2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="pt-3 space-y-3">
                        <button type="submit" class="w-full rounded-xl bg-brand-button px-4 py-3.5 text-sm font-semibold text-white hover:bg-brand-buttonHover shadow-md transition-all active:scale-[0.99]">
                            Simpan Kata Sandi Baru
                        </button>

                        <a href="{{ route('pelapor.login') }}" class="w-full flex justify-center items-center rounded-xl border border-brand-button bg-white px-4 py-3.5 text-sm font-semibold text-brand-button hover:bg-rose-50 transition-all shadow-sm">
                            Kembali ke Masuk
                        </a>
                    </div>
                </form>
            </section>
        </div>
    </main>

    <!-- Script Toggle Password -->
    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);

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