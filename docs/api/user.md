# API Documentation: Authenticated User Endpoints

Endpoint berikut memerlukan autentikasi sebagai pengguna (siswa) dan harus menyertakan `Bearer Token` pada header `Authorization`.

Kembali ke Halaman Utama

---

## 1. Manajemen Akun & Profil

| Method   | Endpoint                          | Deskripsi                                                |
| :------- | :-------------------------------- | :------------------------------------------------------- |
| `POST`   | `/signout`                        | Logout user dan menghapus token saat ini.                |
| `POST`   | `/auth/refresh`                   | Memperbarui token autentikasi yang sudah ada.            |
| `POST`   | `/password/change`                | Mengganti password user yang sedang login.               |
| `GET`    | `/user`                           | Mendapatkan data user yang sedang login beserta rolenya. |
| `GET`    | `/user/profile`                   | Mendapatkan profil lengkap user.                         |
| `PUT`    | `/user/profile`                   | Memperbarui profil user.                                 |
| `PUT`    | `/user/{id}/update-image`         | Memperbarui gambar profil user.                          |
| `DELETE` | `/user-profile/{id}/remove-image` | Menghapus gambar profil user.                            |

## 2. Pendaftaran & Pembelajaran Kursus

| Method | Endpoint                                                                          | Deskripsi                                        |
| :----- | :-------------------------------------------------------------------------------- | :----------------------------------------------- |
| `POST` | `/registration`                                                                   | Mendaftarkan user ke sebuah kursus.              |
| `GET`  | `/user/dashboard`                                                                 | Mendapatkan data ringkasan untuk dashboard user. |
| `GET`  | `/user/courses`                                                                   | Mendapatkan daftar kursus yang diikuti user.     |
| `GET`  | `/user/courses/{id}`                                                              | Mendapatkan detail kursus yang diikuti user.     |
| `GET`  | `/enrollments/{enrollment}/modules/{module}`                                      | Menampilkan konten dari sebuah modul.            |
| `POST` | `/enrollments/{enrollment}/modules/{module}/progress`                             | Memperbarui progress penyelesaian konten.        |
| `GET`  | `/enrollments/{enrollment}/modules/{module}/contents/{content}/start-quiz`        | Memulai sebuah kuis.                             |
| `POST` | `/enrollments/{enrollment}/modules/{module}/contents/{content}/submit-quiz`       | Mengirimkan jawaban kuis.                        |
| `POST` | `/enrollments/{enrollment}/modules/{module}/contents/{content}/submit-assignment` | Mengirimkan file tugas (assignment).             |
| `GET`  | `/enrollments/{enrollment}/modules/{module}/contents/{content}/start-practice`    | Memulai sebuah latihan (practice).               |
| `POST` | `/enrollments/{enrollment}/modules/{module}/contents/{content}/submit-practice`   | Mengirimkan jawaban latihan.                     |

## 3. Pembayaran & Riwayat

| Method | Endpoint                   | Deskripsi                                        |
| :----- | :------------------------- | :----------------------------------------------- |
| `POST` | `/payments/create`         | Membuat transaksi pembayaran baru (Midtrans).    |
| `GET`  | `/payments/{registration}` | Mendapatkan status pembayaran untuk pendaftaran. |
| `GET`  | `/user/payments`           | Mendapatkan riwayat pembayaran user.             |

## 4. Data Pendukung (Legacy & Lainnya)

| Method | Endpoint                       | Deskripsi                                      |
| :----- | :----------------------------- | :--------------------------------------------- |
| `GET`  | `/courses/{id}/with-materials` | Mendapatkan kursus beserta materinya (legacy). |
| `GET`  | `/courses/with-zoom-link`      | Mendapatkan daftar kursus beserta link Zoom.   |
| `GET`  | `/courses/name-and-id`         | Mendapatkan daftar ID dan nama semua kursus.   |
