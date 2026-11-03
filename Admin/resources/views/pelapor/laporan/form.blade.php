@extends('pelapor.layout')

@section('title', $laporan ? 'Edit Pengaduan' : 'Buat Pengaduan')

@section('content')
    <div class="max-w-3xl">
        <a href="{{ $laporan ? route('pelapor.laporan.show', $laporan->id_laporan) : route('pelapor.laporan.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-rose-700 transition hover:gap-3 hover:text-rose-800">
            <span class="grid h-9 w-9 place-items-center rounded-full border border-rose-100 bg-white shadow-sm">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7 7-7-7 7-7"/></svg>
            </span>
            Kembali
        </a>
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
                <div class="mb-1.5 flex flex-wrap items-center justify-between gap-2">
                    <label for="lokasi_kejadian" class="text-sm font-medium">Lokasi Kejadian</label>
                    <button id="openMapPicker" type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-200">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                        Pilih di Peta
                    </button>
                </div>
                <input id="lokasi_kejadian" name="lokasi_kejadian" value="{{ old('lokasi_kejadian', $laporan?->lokasi_kejadian) }}" minlength="4" maxlength="50" required placeholder="Isi alamat atau pilih titik pada peta" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100">
                <div id="selectedMapLocation" class="mt-2 hidden items-center justify-between gap-2 rounded-lg border border-rose-100 bg-rose-50/70 px-3 py-2">
                    <span id="selectedMapLocationText" class="min-w-0 break-words text-xs text-slate-600"></span>
                    <a id="openSelectedLocation" href="#" target="_blank" rel="noopener noreferrer" class="shrink-0 text-xs font-semibold text-rose-700 hover:underline">Buka Maps</a>
                </div>
                <p class="mt-1 text-xs text-slate-500">Masukkan alamat atau pilih lokasi langsung dari peta.</p>
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

    <dialog id="mapPickerDialog" class="w-[min(48rem,calc(100%-2rem))] overflow-hidden rounded-2xl border-0 p-0 shadow-2xl backdrop:bg-slate-900/60">
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Pilih Lokasi Kejadian</h2>
                <p class="mt-1 text-xs text-slate-500">Cari alamat atau klik titik yang sesuai pada peta.</p>
            </div>
            <button id="closeMapPicker" type="button" aria-label="Tutup peta" class="grid h-9 w-9 shrink-0 place-items-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-800">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18"/></svg>
            </button>
        </div>
        <div class="space-y-3 px-5 py-4 sm:px-6">
            <form id="mapSearchForm" class="flex flex-col gap-2 sm:flex-row">
                <input id="mapSearchInput" type="search" autocomplete="off" placeholder="Cari nama jalan, desa, atau kota" class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-rose-500 focus:ring-2 focus:ring-rose-100">
                <div class="flex gap-2">
                <button id="currentLocationButton" type="button" class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-rose-200 bg-white px-3 py-2.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50 disabled:cursor-wait disabled:opacity-60 sm:flex-none sm:text-sm">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" d="M12 2v3m0 14v3M2 12h3m14 0h3"/><circle cx="12" cy="12" r="8"/></svg>
                    <span id="currentLocationButtonText">Lokasi Saat Ini</span>
                </button>
                <button id="mapSearchButton" type="submit" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700 disabled:cursor-wait disabled:opacity-60">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-4-4"/></svg>
                    Cari
                </button>
                </div>
            </form>
            <p id="mapPickerMessage" role="status" aria-live="polite" class="min-h-5 text-xs text-slate-500">Gunakan lokasi saat ini, cari alamat, atau klik titik pada peta.</p>
            <div id="locationMap" class="z-0 h-[min(55vh,24rem)] min-h-64 overflow-hidden rounded-xl border border-slate-200 bg-slate-100"></div>
            <div class="flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                <p id="mapSelectedAddress" class="min-w-0 text-xs leading-5 text-slate-600">Belum ada lokasi yang dipilih.</p>
                <div class="flex shrink-0 gap-2">
                    <button id="cancelMapPicker" type="button" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Batal</button>
                    <button id="confirmMapLocation" type="button" disabled class="rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-50">Gunakan Lokasi</button>
                </div>
            </div>
        </div>
    </dialog>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
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
        const locationInput = document.getElementById('lokasi_kejadian');
        const mapPickerDialog = document.getElementById('mapPickerDialog');
        const mapPickerMessage = document.getElementById('mapPickerMessage');
        const mapSelectedAddress = document.getElementById('mapSelectedAddress');
        const confirmMapLocation = document.getElementById('confirmMapLocation');
        let locationMap;
        let locationMarker;
        let selectedMapLocation = null;
        let reverseGeocodeTimer;
        let reverseGeocodeRequest = 0;

        function setMapMessage(message, isError = false) {
            mapPickerMessage.textContent = message;
            mapPickerMessage.classList.toggle('text-rose-600', isError);
            mapPickerMessage.classList.toggle('text-slate-500', !isError);
        }

        function locationLabel(address, latitude, longitude) {
            const details = address?.address || {};
            const pieces = [
                details.road || details.pedestrian,
                details.neighbourhood || details.suburb || details.quarter,
                details.village || details.town || details.city || details.county,
                details.state,
            ].filter(Boolean);
            const uniquePieces = [...new Set(pieces)];
            const label = uniquePieces.slice(0, 3).join(', ');
            return label.length >= 4 && label.length <= 50
                ? label
                : `Koordinat ${latitude.toFixed(5)}, ${longitude.toFixed(5)}`;
        }

        function updateSelectedLocation(latitude, longitude, label = null) {
            selectedMapLocation = {
                latitude,
                longitude,
                label: label || `Koordinat ${latitude.toFixed(5)}, ${longitude.toFixed(5)}`,
            };
            if (locationMarker) {
                locationMarker.setLatLng([latitude, longitude]);
            } else {
                locationMarker = L.marker([latitude, longitude]).addTo(locationMap);
            }
            locationMap.setView([latitude, longitude], 16);
            mapSelectedAddress.textContent = selectedMapLocation.label;
            confirmMapLocation.disabled = false;
        }

        function showLocationMapLink(label, coordinates = null) {
            const preview = document.getElementById('selectedMapLocation');
            const mapsLink = document.getElementById('openSelectedLocation');
            document.getElementById('selectedMapLocationText').textContent = label;
            const query = coordinates ? `${coordinates.latitude},${coordinates.longitude}` : label;
            mapsLink.href = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(query)}`;
            preview.classList.remove('hidden');
            preview.classList.add('flex');
        }

        function reverseGeocodeLocation(latitude, longitude) {
            const requestId = ++reverseGeocodeRequest;
            updateSelectedLocation(latitude, longitude);
            setMapMessage('Mencari nama alamat...');
            clearTimeout(reverseGeocodeTimer);
            reverseGeocodeTimer = setTimeout(async () => {
                try {
                    const url = new URL('https://nominatim.openstreetmap.org/reverse');
                    url.search = new URLSearchParams({
                        format: 'jsonv2',
                        lat: latitude,
                        lon: longitude,
                        zoom: '18',
                        addressdetails: '1',
                    });
                    const response = await fetch(url, { headers: { Accept: 'application/json' } });
                    if (!response.ok) throw new Error('Layanan pencarian alamat sedang tidak tersedia.');
                    const result = await response.json();
                    if (requestId !== reverseGeocodeRequest) return;
                    const label = locationLabel(result, latitude, longitude);
                    updateSelectedLocation(latitude, longitude, label);
                    setMapMessage('Lokasi ditemukan. Pilih “Gunakan Lokasi” untuk mengisi formulir.');
                } catch (error) {
                    if (requestId !== reverseGeocodeRequest) return;
                    setMapMessage(`${error.message} Koordinat tetap dapat digunakan sebagai lokasi.`, true);
                }
            }, 1000);
        }

        function initializeLocationMap() {
            if (typeof L === 'undefined') {
                setMapMessage('Peta tidak dapat dimuat. Periksa koneksi internet lalu coba lagi.', true);
                return;
            }
            if (locationMap) {
                locationMap.invalidateSize();
                return;
            }

            const coordinateMatch = locationInput.value.match(/(-?\d+(?:\.\d+)?)[,\s]+(-?\d+(?:\.\d+)?)/);
            const initialCenter = coordinateMatch
                ? [Number(coordinateMatch[1]), Number(coordinateMatch[2])]
                : [-8.1724, 113.7003];
            locationMap = L.map('locationMap').setView(initialCenter, coordinateMatch ? 15 : 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a>',
            }).addTo(locationMap);
            locationMap.on('click', event => reverseGeocodeLocation(event.latlng.lat, event.latlng.lng));

            if (coordinateMatch) {
                updateSelectedLocation(Number(coordinateMatch[1]), Number(coordinateMatch[2]), locationInput.value);
                setMapMessage('Titik dari lokasi sebelumnya ditampilkan. Klik peta untuk mengubahnya.');
            }
        }

        function locateCurrentPosition() {
            if (!locationMap) {
                setMapMessage('Peta belum siap. Periksa koneksi internet lalu coba lagi.', true);
                return;
            }
            if (!('geolocation' in navigator)) {
                setMapMessage('Browser ini tidak mendukung akses lokasi. Cari alamat atau pilih titik pada peta.', true);
                return;
            }

            const button = document.getElementById('currentLocationButton');
            const buttonText = document.getElementById('currentLocationButtonText');
            button.disabled = true;
            buttonText.textContent = 'Mencari lokasi...';
            setMapMessage('Meminta izin lokasi dari browser...');
            navigator.geolocation.getCurrentPosition(
                position => {
                    const { latitude, longitude } = position.coords;
                    locationMap.setView([latitude, longitude], 16);
                    reverseGeocodeLocation(latitude, longitude);
                    setMapMessage('Lokasi perangkat ditemukan. Sedang mencari nama alamat...');
                    button.disabled = false;
                    buttonText.textContent = 'Lokasi Saat Ini';
                },
                error => {
                    const messages = {
                        1: 'Izin lokasi ditolak. Izinkan akses lokasi untuk situs 127.0.0.1 di pengaturan browser, lalu coba lagi.',
                        2: 'Lokasi perangkat tidak tersedia. Aktifkan layanan lokasi atau pilih titik pada peta.',
                        3: 'Permintaan lokasi terlalu lama. Coba lagi atau pilih titik pada peta.',
                    };
                    setMapMessage(messages[error.code] || 'Lokasi tidak dapat diakses. Pilih titik pada peta.', true);
                    button.disabled = false;
                    buttonText.textContent = 'Lokasi Saat Ini';
                },
                {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 60000,
                }
            );
        }

        document.getElementById('openMapPicker').addEventListener('click', () => {
            mapPickerDialog.showModal();
            setMapMessage('Gunakan lokasi saat ini, cari alamat, atau klik titik pada peta.');
            initializeLocationMap();
            if (locationMap) setTimeout(() => locationMap.invalidateSize(), 100);
            const currentLocation = locationInput.value.trim();
            document.getElementById('mapSearchInput').value = currentLocation;
            if (currentLocation && selectedMapLocation?.label !== currentLocation) {
                document.getElementById('mapSearchInput').dispatchEvent(new Event('submit'));
            }
        });

        locationInput.addEventListener('input', () => {
            const value = locationInput.value.trim();
            if (!value) {
                document.getElementById('selectedMapLocation').classList.add('hidden');
                return;
            }
            const coordinates = selectedMapLocation?.label === value ? selectedMapLocation : null;
            showLocationMapLink(value, coordinates);
        });

        document.getElementById('closeMapPicker').addEventListener('click', () => mapPickerDialog.close());
        document.getElementById('cancelMapPicker').addEventListener('click', () => mapPickerDialog.close());
        document.getElementById('currentLocationButton').addEventListener('click', locateCurrentPosition);

        document.getElementById('mapSearchForm').addEventListener('submit', async event => {
            event.preventDefault();
            const query = document.getElementById('mapSearchInput').value.trim();
            if (!query) {
                setMapMessage('Masukkan alamat atau nama tempat yang ingin dicari.', true);
                return;
            }
            if (!locationMap) {
                setMapMessage('Peta belum siap. Muat ulang halaman atau periksa koneksi internet.', true);
                return;
            }
            const searchButton = document.getElementById('mapSearchButton');
            searchButton.disabled = true;
            setMapMessage('Sedang mencari lokasi...');
            try {
                const url = new URL('https://nominatim.openstreetmap.org/search');
                url.search = new URLSearchParams({
                    format: 'jsonv2',
                    q: query,
                    limit: '1',
                    countrycodes: 'id',
                    addressdetails: '1',
                });
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Layanan pencarian lokasi sedang tidak tersedia.');
                const results = await response.json();
                if (!results.length) throw new Error('Lokasi tidak ditemukan. Coba gunakan nama jalan atau kota lain.');
                const result = results[0];
                const latitude = Number(result.lat);
                const longitude = Number(result.lon);
                updateSelectedLocation(latitude, longitude, locationLabel(result, latitude, longitude));
                setMapMessage('Lokasi ditemukan. Pilih “Gunakan Lokasi” untuk mengisi formulir.');
            } catch (error) {
                setMapMessage(error.message || 'Pencarian lokasi gagal. Silakan coba kembali.', true);
            } finally {
                searchButton.disabled = false;
            }
        });

        document.getElementById('confirmMapLocation').addEventListener('click', () => {
            if (!selectedMapLocation) return;
            locationInput.value = selectedMapLocation.label;
            locationInput.dispatchEvent(new Event('input', { bubbles: true }));
            showLocationMapLink(selectedMapLocation.label, selectedMapLocation);
            mapPickerDialog.close();
        });

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
            document.getElementById('summaryLocation').textContent = locationInput.value;
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
            error.textContent = '';
            photoInput.setCustomValidity('');
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