@extends('pelapor.layout')

@section('title', $laporan ? 'Edit Pengaduan' : 'Buat Pengaduan')

@section('content')
    <div class="max-w-3xl">
        <a href="{{ $laporan ? route('pelapor.laporan.show', $laporan->id_laporan) : route('pelapor.laporan.index') }}" class="text-sm font-medium text-rose-700 hover:underline">← Kembali</a>
        <h1 class="mt-3 text-2xl font-bold">{{ $laporan ? 'Edit Pengaduan' : 'Buat Pengaduan' }}</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Ceritakan kejadian secara jelas. Data pengaduan hanya dapat dilihat oleh akun Anda dan petugas berwenang.</p>

        @if ($errors->any())
            <div role="alert" class="mt-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
        @endif

        <form id="reportForm" method="POST" enctype="multipart/form-data" action="{{ $laporan ? route('pelapor.laporan.update', $laporan->id_laporan) : route('pelapor.laporan.store') }}" class="mt-6 space-y-5 rounded-xl border border-slate-200 bg-white p-5 sm:p-7" data-existing-photos="{{ count($fotoTersimpan) }}">
            @csrf
            @if ($laporan) @method('PUT') @endif
            <div>
                <label for="kategori" class="mb-1.5 block text-sm font-medium">Kategori Laporan</label>
                <select id="kategori" name="kategori" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
                    <option value="">Pilih kategori</option>
                    @foreach ($kategori as $option)
                        <option value="{{ $option }}" @selected(old('kategori', $laporan?->kategori) === $option)>{{ $option }}</option>
                    @endforeach
                </select>
                @error('kategori')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="sebagai" class="mb-1.5 block text-sm font-medium">Melapor Sebagai</label>
                <select id="sebagai" name="sebagai" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
                    <option value="">Pilih peran</option>
                    @foreach ($peranPelapor as $option)
                        <option value="{{ $option }}" @selected(old('sebagai', $laporan?->sebagai) === $option)>{{ $option }}</option>
                    @endforeach
                </select>
                @error('sebagai')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="lokasi_kejadian" class="mb-1.5 block text-sm font-medium">Lokasi Kejadian</label>
                <input id="lokasi_kejadian" name="lokasi_kejadian" value="{{ old('lokasi_kejadian', $laporan?->lokasi_kejadian) }}" minlength="4" maxlength="50" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
                @error('lokasi_kejadian')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="tanggal_kejadian" class="mb-1.5 block text-sm font-medium">Tanggal Kejadian</label>
                <input id="tanggal_kejadian" name="tanggal_kejadian" type="date" max="{{ now()->toDateString() }}" value="{{ old('tanggal_kejadian', $laporan?->tanggal_kejadian?->format('Y-m-d')) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
                @error('tanggal_kejadian')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="kronologi" class="mb-1.5 block text-sm font-medium">Penjelasan Kejadian</label>
                <textarea id="kronologi" name="kronologi" rows="6" minlength="100" maxlength="5000" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">{{ old('kronologi', $laporan?->kronologi) }}</textarea>
                @error('kronologi')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <p id="formClientError" role="alert" class="hidden rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700"></p>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ $laporan ? route('pelapor.laporan.show', $laporan->id_laporan) : route('pelapor.laporan.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
                <button id="openConfirmation" type="button" class="rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">Lanjut ke Konfirmasi</button>
            </div>
        </form>
    </div>

    <dialog id="confirmationDialog" class="w-[min(42rem,calc(100%-2rem))] max-h-[90vh] overflow-y-auto rounded-xl p-0 shadow-2xl backdrop:bg-slate-900/50">
        <div class="border-b border-slate-100 px-5 py-4 sm:px-7"><h2 class="text-lg font-bold">Konfirmasi Pengaduan</h2><p class="mt-1 text-sm text-slate-500">Periksa data sebelum mengirim. Unggah minimal 1 dan maksimal 3 foto JPG, JPEG, atau PNG.</p></div>
        <div class="space-y-5 px-5 py-5 sm:px-7">
            <dl class="grid gap-4 text-sm sm:grid-cols-2">
                <div><dt class="text-xs text-slate-500">Kategori</dt><dd id="summaryCategory" class="mt-1 font-semibold"></dd></div>
                <div><dt class="text-xs text-slate-500">Melapor sebagai</dt><dd id="summaryAs" class="mt-1 font-semibold"></dd></div>
                <div><dt class="text-xs text-slate-500">Lokasi</dt><dd id="summaryLocation" class="mt-1 font-semibold"></dd></div>
                <div><dt class="text-xs text-slate-500">Tanggal Kejadian</dt><dd id="summaryDate" class="mt-1 font-semibold"></dd></div>
            </dl>
            <div><p class="text-xs text-slate-500">Penjelasan Kejadian</p><p id="summaryStory" class="mt-1 max-h-32 overflow-y-auto whitespace-pre-line rounded-lg bg-slate-50 p-3 text-sm leading-6"></p></div>
            <div>
                <label for="foto" class="block text-sm font-semibold">Bukti Foto <span class="text-rose-700">*</span></label>
                @if (count($fotoTersimpan) > 0)
                    <p class="mt-1 text-xs text-slate-500">{{ count($fotoTersimpan) }} foto sudah tersimpan. Jika Anda memilih foto baru di bawah ini, foto baru akan digunakan (maksimal 3 foto).</p>
                @else
                    <p class="mt-1 text-xs text-slate-500">Wajib 1–3 foto. Format JPG, JPEG, PNG; maksimal 5 MB per foto.</p>
                @endif
                <input id="foto" name="foto[]" type="file" form="reportForm" accept=".jpg,.jpeg,.png,image/jpeg,image/png" multiple class="mt-3 block w-full rounded-lg border border-slate-300 p-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-rose-50 file:px-3 file:py-2 file:font-semibold file:text-rose-700">
                <p id="photoClientError" role="alert" class="mt-2 hidden text-sm text-rose-700"></p>
                <div id="photoPreview" class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3"></div>
                @if (count($fotoTersimpan) > 0)
                    <div id="existingPhotosSection" class="mt-3">
                        <p class="text-xs text-slate-400 mb-2">Foto tersimpan saat ini:</p>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach ($fotoTersimpan as $index => $path)
                                @if (str_starts_with($path, 'laporan/' . $laporan->id_laporan . '/'))
                                    <img src="{{ route('pelapor.laporan.evidence', [$laporan->id_laporan, $index]) }}" alt="Bukti foto tersimpan" class="aspect-square w-full rounded-lg border border-slate-200 object-cover">
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="flex flex-col-reverse gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end sm:px-7">
            <button id="backToEdit" type="button" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold hover:bg-slate-50">Kembali Perbaiki</button>
            <button id="submitReport" type="submit" form="reportForm" class="rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">Kirim Pengaduan</button>
        </div>
    </dialog>

    <script>
        const reportForm = document.getElementById('reportForm');
        const confirmationDialog = document.getElementById('confirmationDialog');
        const photoInput = document.getElementById('foto');
        const existingPhotoCount = Number(reportForm.dataset.existingPhotos);
        const allowedPhotoExtensions = ['jpg', 'jpeg', 'png'];

        document.getElementById('openConfirmation').addEventListener('click', () => {
            const error = document.getElementById('formClientError');
            error.classList.add('hidden');
            if (!reportForm.reportValidity()) return;
            const story = document.getElementById('kronologi').value.trim();
            if (!/\p{L}/u.test(story)) {
                error.textContent = 'Penjelasan kejadian harus mengandung huruf.';
                error.classList.remove('hidden');
                document.getElementById('kronologi').focus();
                return;
            }
            document.getElementById('summaryCategory').textContent = document.getElementById('kategori').value;
            document.getElementById('summaryAs').textContent = document.getElementById('sebagai').value;
            document.getElementById('summaryLocation').textContent = document.getElementById('lokasi_kejadian').value;
            document.getElementById('summaryDate').textContent = document.getElementById('tanggal_kejadian').value;
            document.getElementById('summaryStory').textContent = story;
            photoInput.required = existingPhotoCount === 0;
            confirmationDialog.showModal();
        });

        document.getElementById('backToEdit').addEventListener('click', () => confirmationDialog.close());

        photoInput.addEventListener('change', () => {
            const error = document.getElementById('photoClientError');
            const preview = document.getElementById('photoPreview');
            preview.replaceChildren();
            error.classList.add('hidden');
            const files = Array.from(photoInput.files || []);
            
            const allowedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/pjpeg'];
            
            const invalid = files.find(file => {
                const extension = file.name.split('.').pop().toLowerCase();
                const isValidExt = allowedPhotoExtensions.includes(extension);
                const isValidMime = !file.type || allowedMimeTypes.includes(file.type);
                return !isValidExt || !isValidMime || file.size > 5 * 1024 * 1024;
            });

            // Jika mengunggah foto baru, batasi maksimal 3 foto baru yang dipilih
            if (files.length > 3) {
                error.textContent = 'Maksimal 3 foto bukti baru yang dapat diunggah.';
            } else if (invalid) {
                error.textContent = invalid.size > 5 * 1024 * 1024 ? 'Ukuran setiap foto maksimal 5 MB.' : 'Format foto yang diperbolehkan hanya JPG, JPEG, dan PNG.';
            }

            if (error.textContent) {
                error.classList.remove('hidden');
                photoInput.setCustomValidity(error.textContent);
            } else {
                photoInput.setCustomValidity('');
                files.forEach(file => {
                    const image = document.createElement('img');
                    image.src = URL.createObjectURL(file);
                    image.alt = 'Pratinjau ' + file.name;
                    image.className = 'aspect-square w-full rounded-lg border border-slate-200 object-cover';
                    preview.append(image);
                });
            }
        });

        reportForm.addEventListener('submit', event => {
            if (!confirmationDialog.open) {
                event.preventDefault();
                return;
            }
            // Jika belum ada foto sama sekali (baik foto tersimpan maupun baru)
            if (existingPhotoCount === 0 && (photoInput.files?.length || 0) < 1) {
                event.preventDefault();
                document.getElementById('photoClientError').textContent = 'Unggah minimal 1 foto bukti.';
                document.getElementById('photoClientError').classList.remove('hidden');
            } else if (!photoInput.checkValidity()) {
                event.preventDefault();
                photoInput.reportValidity();
            }
        });
    </script>
@endsection