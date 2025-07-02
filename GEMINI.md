# 📘 Laravel Backend – Modular Learning Platform (HBS-style)

## 📌 Project Overview

Ini adalah backend Laravel 10 untuk sebuah platform pembelajaran online yang terinspirasi dari struktur Harvard Business School (HBS). Aplikasi ini memungkinkan siswa mendaftar ke kursus, mengikuti modul-modul pembelajaran secara berurutan, dan menyelesaikan berbagai jenis konten di dalam setiap modul.

Frontend dibangun menggunakan **Vue 3 + Quasar Framework**, dan terintegrasi dengan API Laravel melalui token autentikasi.

---

## 🧩 Fitur Utama

### 🧑‍🎓 Enrollment

-   Siswa dapat mendaftar ke kursus tertentu.
-   Setiap enrollment memiliki progress individual terhadap setiap modul dan konten.

### 📦 Course & Module Structure

-   Kursus terdiri dari beberapa **modul terurut (ordered)**.
-   Modul terdiri dari beberapa **konten terurut**.

### 📚 Jenis Konten yang Didukung

1. **Video** – streaming dan unduhan
2. **Text** – HTML atau Markdown
3. **File** – untuk diunduh siswa
4. **Quiz** – soal interaktif, dengan skor dan batas waktu
5. **Assignment** – unggahan tugas dengan batas ukuran dan waktu
6. **Practice (Latihan)** – konten interaktif untuk penguatan materi

---

## 🔐 Autentikasi & Autorisasi

-   Menggunakan Laravel **Sanctum** untuk token-based authentication.
-   Hanya siswa yang memiliki enrollment aktif yang bisa mengakses konten kursus.

---

## 📊 Progress Tracking

-   Progres dihitung otomatis berdasarkan penyelesaian setiap konten.
-   Tersedia endpoint untuk memperbarui status konten (`/progress`).
-   Jika seluruh konten modul diselesaikan → modul dianggap selesai.

---

## 🏅 Sertifikasi

Setelah siswa menyelesaikan semua modul atau kursus, sistem dapat menghasilkan **sertifikat digital** yang berisi informasi berikut:

### **Rencana Struktur Sertifikat:**

1. **Nama Siswa**
2. **Nama Kursus**
3. **Tanggal Penyelesaian**
4. **Skor (jika ada)**
5. **Nomor Sertifikat Unik**

### Langkah-langkah untuk mendapatkan sertifikat:

1. **Kriteria Penyelesaian:**
    - Sertifikat hanya dikeluarkan setelah seluruh konten kursus atau modul diselesaikan (tergantung pengaturan sistem).
    - Semua modul dalam kursus harus memiliki status `completed` untuk kursus dianggap selesai.
2. **Format Sertifikat:**
    - Sertifikat akan di-generate dalam format **PDF** yang dapat diunduh oleh siswa.
    - Sertifikat dapat berisi elemen desain seperti logo institusi dan tanda tangan digital (jika diinginkan).
