# API Documentation: Public Endpoints

Endpoint berikut dapat diakses oleh siapa saja tanpa memerlukan token autentikasi.

[Kembali ke Halaman Utama](./index.md)

---

## 1. Autentikasi & Manajemen Akun

| Method | Endpoint                    | Deskripsi                                        |
| :----- | :-------------------------- | :----------------------------------------------- |
| `POST` | `/signup`                   | Mendaftarkan user baru.                          |
| `POST` | `/signin`                   | Login user dan mendapatkan token autentikasi.    |
| `GET`  | `/auth/google`              | Mendapatkan URL redirect untuk login via Google. |
| `GET`  | `/auth/google/callback`     | Callback setelah otentikasi Google berhasil.     |
| `POST` | `/password/email`           | Mengirim kode reset password ke email.           |
| `POST` | `/password/code/check`      | Memverifikasi kode reset password.               |
| `POST` | `/password/reset`           | Mereset password menggunakan kode yang valid.    |
| `GET`  | `/email/verify/{id}/{hash}` | Memverifikasi alamat email (dari link email).    |
| `POST` | `/email/verify/resend`      | Mengirim ulang email verifikasi.                 |

## 2. Data Kursus (Courses)

| Method | Endpoint                    | Deskripsi                                         |
| :----- | :-------------------------- | :------------------------------------------------ |
| `GET`  | `/course`                   | Mendapatkan daftar semua kursus.                  |
| `GET`  | `/course/{id}`              | Mendapatkan detail satu kursus.                   |
| `GET`  | `/courses/name/{id}`        | Mendapatkan nama kursus berdasarkan ID.           |
| `GET`  | `/courses/{id}/related`     | Mendapatkan daftar kursus terkait.                |
| `GET`  | `/course/{id}/with-modules` | Mendapatkan detail kursus beserta modul-modulnya. |

## 3. Data Trainer

| Method | Endpoint                                       | Deskripsi                                         |
| :----- | :--------------------------------------------- | :------------------------------------------------ |
| `GET`  | `/trainers`                                    | Mendapatkan daftar semua trainer.                 |
| `GET`  | `/starred-trainers`                            | Mendapatkan daftar trainer favorit.               |
| `GET`  | `/trainers/{trainer}`                          | Mendapatkan detail satu trainer.                  |
| `GET`  | `/trainers/qualification/{qualification}/{id}` | Mendapatkan trainer dengan kualifikasi yang sama. |

## 4. Lain-lain (FAQ, Contact, Benefits)

| Method | Endpoint                             | Deskripsi                                         |
| :----- | :----------------------------------- | :------------------------------------------------ |
| `GET`  | `/faqs`                              | Mendapatkan semua data FAQ.                       |
| `GET`  | `/faqs/{faq}`                        | Mendapatkan detail satu FAQ.                      |
| `POST` | `/contacts`                          | Mengirim pesan kontak dari user.                  |
| `GET`  | `/course/{courseId}/course_benefits` | Mendapatkan daftar benefit untuk kursus tertentu. |

## 5. Pembayaran (Callback)

Endpoint ini digunakan oleh pihak ketiga (payment gateway) untuk mengirim notifikasi.

| Method | Endpoint                  | Deskripsi                                |
| :----- | :------------------------ | :--------------------------------------- |
| `POST` | `/payments/callback`      | Endpoint callback dari payment gateway.  |
| `POST` | `/payments/status/update` | (Internal) Mengupdate status pembayaran. |
