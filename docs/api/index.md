# Dokumentasi API - Platform Pembelajaran Modular

Selamat datang di dokumentasi API untuk platform pembelajaran modular. Dokumen ini bertujuan untuk menyediakan panduan yang jelas dan komprehensif bagi para developer yang akan berinteraksi dengan backend.

## Base URL

Semua endpoint API yang dijelaskan dalam dokumentasi ini menggunakan base URL berikut:

```
https://your-domain.com/api
```

---

## 🔐 Autentikasi

Sebagian besar endpoint memerlukan autentikasi menggunakan token. Sistem ini menggunakan **Laravel Sanctum** untuk otentikasi berbasis token.

Untuk mengakses endpoint yang terproteksi, sertakan token yang didapat saat login pada header `Authorization` dengan skema `Bearer`.

**Contoh Header:**

```
Authorization: Bearer <your_api_token>
```

Token memiliki masa berlaku. Jika token kedaluwarsa, gunakan endpoint `POST /auth/refresh` untuk mendapatkan token baru.

---

## 🗂️ Kategori Endpoint

Dokumentasi API ini dibagi berdasarkan peran pengguna untuk memudahkan navigasi:

-   **Endpoint Publik**
    -   Endpoint yang dapat diakses oleh siapa saja tanpa perlu autentikasi. Contoh: melihat daftar kursus, mendaftar, dan login.
-   **Endpoint Pengguna (Siswa)**
    -   Endpoint yang memerlukan autentikasi sebagai pengguna (siswa). Contoh: mengakses materi kursus, melihat progress, dan mengelola profil.
-   **Endpoint Admin**
    -   Endpoint yang memerlukan autentikasi sebagai administrator. Contoh: mengelola kursus, modul, pengguna, dan konten lainnya.
