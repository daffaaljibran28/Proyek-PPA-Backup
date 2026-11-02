# SAPA PPA

Project gabungan ini memakai Laravel untuk admin/API dan Flutter untuk aplikasi pengguna:

- Admin dan API: `Admin `
- Aplikasi pengguna: `Pengguna `


## Persyaratan

- PHP 8.2 atau lebih baru dan Composer
- MySQL
- Flutter 3 dengan Dart 3

## Menyiapkan database dan API

1. Buat database MySQL kosong, misalnya `db_sapa`.
2. Dari folder `Admin 1`, buat `.env` hanya jika belum ada:

   ```powershell
   if (!(Test-Path .env)) { Copy-Item .env.example .env }
   ```

   Isi `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` sesuai MySQL lokal Anda. Isi juga `ADMIN_EMAIL` dan `ADMIN_PASSWORD` dengan email dan password admin kuat (minimal 12 karakter). Jangan mengganti `.env` yang sudah berisi pengaturan lokal.
3. Jalankan:

   ```powershell
   composer install
   php artisan key:generate
   php artisan migrate
   php artisan db:seed
   php artisan serve --host=0.0.0.0 --port=8000
   ```

4. Buka `http://localhost:8000/login` untuk admin. Pengguna Flutter dapat membuat akun melalui aplikasi. Akun dan laporan disimpan pada database Laravel yang sama; lampiran disimpan pada storage privat Laravel.

Jalankan `php artisan key:generate` hanya pada instalasi baru atau jika `APP_KEY` masih kosong. Jangan mengganti kunci instalasi yang sudah berjalan. Jangan jalankan migrasi pada database produksi tanpa backup terlebih dahulu.

## Menjalankan Flutter

Jalankan perintah dari folder `Pengguna 2`:

```powershell
flutter pub get
flutter run
```

URL API default:

- Android Emulator: `http://10.0.2.2:8000/api`
- iOS Simulator / desktop: `http://127.0.0.1:8000/api`
- Flutter Web: host browser dengan port API `8000`

Untuk perangkat fisik, gunakan alamat IP komputer yang menjalankan Laravel dan pastikan perangkat berada di jaringan yang sama:

```powershell
flutter run --dart-define=API_BASE_URL=http://192.168.1.10:8000/api
```

Ganti alamat tersebut sesuai IP komputer Anda. HTTP hanya diizinkan pada Android debug untuk pengembangan lokal. Gunakan HTTPS untuk build produksi.

Login Flutter menggunakan email atau nomor HP. Token sesi disimpan pada perangkat dan dikirim untuk API laporan, notifikasi, serta profil.
