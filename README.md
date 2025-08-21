# Sertifikasi App

Sertifikasi App adalah aplikasi web berbasis Laravel untuk manajemen sertifikasi yang memungkinkan pengelolaan pendaftaran peserta, ujian, dan penerbitan sertifikat secara efisien.

## Teknologi yang Digunakan

-   [Laravel](https://laravel.com/) - Framework PHP
-   [Blade](https://laravel.com/docs/blade) - Template Engine
-   MySQL/PostgreSQL - Database
-   [Laravel Breeze/Jetstream] - Authentication
-   [Laravel Sanctum] - API Authentication (jika diperlukan)

## Prasyarat

Sebelum memulai, pastikan sistem Anda memiliki:

-   PHP >= 8.1
-   [Composer](https://getcomposer.org/)
-   MySQL/PostgreSQL
-   Node.js & NPM
-   [Git](https://git-scm.com/)

## Instalasi

1. Clone repository

```bash
git clone https://github.com/raflisetiawan/sertifikasi-app.git
cd sertifikasi-app
```

2. Install dependensi PHP

```bash
composer install
```

3. Install dependensi Node.js

```bash
npm install
```

4. Salin file .env

```bash
cp .env.example .env
```

5. Generate application key

```bash
php artisan key:generate
```

6. Konfigurasi database di file .env

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sertifikasi_app
DB_USERNAME=root
DB_PASSWORD=
```

7. Jalankan migrasi database

```bash
php artisan migrate --seed
```

## Menjalankan Aplikasi

1. Start development server

```bash
php artisan serve
```

2. Compile assets (dalam terminal terpisah)

```bash
npm run dev
```

Aplikasi akan berjalan di `http://localhost:8000`

## Fitur

-   Manajemen Peserta

    -   Registrasi peserta
    -   Profil dan riwayat sertifikasi
    -   Upload dokumen persyaratan

-   Manajemen Sertifikasi

    -   Pembuatan skema sertifikasi
    -   Penjadwalan ujian
    -   Penilaian dan kelulusan
    -   Penerbitan sertifikat

-   Dashboard Admin
    -   Kelola pengguna
    -   Laporan dan statistik
    -   Pengaturan sistem

## Struktur Direktori

```
sertifikasi-app/
├── app/
│   ├── Console/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   └── Providers/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
├── routes/
├── storage/
├── tests/
└── vendor/
```

## Testing

Jalankan test dengan perintah:

```bash
php artisan test
```

## Deployment

1. Set environment production di `.env`

```env
APP_ENV=production
APP_DEBUG=false
```

2. Optimize aplikasi

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. Install dependensi production

```bash
composer install --no-dev --optimize-autoloader
```

4. Compile assets untuk production

```bash
npm run build
```

## Kontribusi

1. Fork repository
2. Buat branch fitur (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## Author

**Rafli Setiawan**

-   GitHub: [@raflisetiawan](https://github.com/raflisetiawan)

## License

Project ini dilisensikan di bawah [MIT License](LICENSE).

## Catatan Pengembang

-   [ ] Implementasi notifikasi email
-   [ ] Integrasi pembayaran
-   [ ] Sistem backup otomatis
-   [ ] Optimasi performa query
-   [ ] Implementasi API endpoints

## Support

Jika Anda menemukan bug atau memiliki saran, silakan buat issue di repository ini.
