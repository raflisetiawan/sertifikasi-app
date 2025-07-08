# API Documentation: Admin Endpoints

Endpoint berikut memerlukan autentikasi sebagai **Admin** dan harus menyertakan `Bearer Token` pada header `Authorization`.

Kembali ke Halaman Utama

---

## 1. Manajemen Kursus (Course)

| Method   | Endpoint                          | Deskripsi                                                 |
| :------- | :-------------------------------- | :-------------------------------------------------------- |
| `POST`   | `/course`                         | Membuat kursus baru.                                      |
| `PATCH`  | `/course/{id}`                    | Memperbarui data kursus.                                  |
| `DELETE` | `/course/{id}`                    | Menghapus kursus.                                         |
| `PATCH`  | `/course/updateStatus/{id}`       | Mengubah status kursus (not_started, ongoing, completed). |
| `POST`   | `/course/{id}/upload-certificate` | Mengunggah template sertifikat untuk kursus.              |

## 2. Manajemen Modul & Konten

### 2.1. Modul

| Method   | Endpoint                      | Deskripsi                                   |
| :------- | :---------------------------- | :------------------------------------------ |
| `GET`    | `/courses/{courseId}/modules` | Mendapatkan semua modul dari sebuah kursus. |
| `POST`   | `/modules`                    | Membuat modul baru.                         |
| `GET`    | `/modules/{id}`               | Mendapatkan detail sebuah modul.            |
| `PUT`    | `/modules/{id}`               | Memperbarui sebuah modul.                   |
| `DELETE` | `/modules/{id}`               | Menghapus sebuah modul.                     |
| `POST`   | `/modules/reorder`            | Mengubah urutan modul.                      |

### 2.2. Konten (Struktur Baru)

**Prefix**: `/admin`
| Method | Endpoint | Deskripsi |
| :------- | :----------------------------------------- | :--------------------------------------------------- |
| `GET` | `/admin/modules/{module}/contents` | Mendapatkan semua konten dari sebuah modul. |
| `POST` | `/admin/modules/{module}/contents` | Menambahkan konten baru ke sebuah modul. |
| `GET` | `/admin/modules/{module}/contents/{content}` | Mendapatkan detail sebuah konten. |
| `PUT` | `/admin/modules/{module}/contents/{content}` | **Memperbarui (edit) sebuah konten.** |
| `DELETE` | `/admin/modules/{module}/contents/{content}` | Menghapus sebuah konten dari modul. |
| `POST` | `/admin/modules/{module}/contents/reorder` | Mengubah urutan konten dalam sebuah modul. |

### 2.3. Manajemen Tipe Konten Spesifik

**Prefix**: `/admin`

Setiap tipe konten di bawah ini memiliki endpoint CRUD (Create, Read, Update, Delete) yang lengkap. Metode `PUT` atau `PATCH` digunakan untuk **mengedit/memperbarui** data.

| Tipe Konten    | Endpoint             | Metode yang Didukung                                              |
| :------------- | :------------------- | :---------------------------------------------------------------- |
| **Text**       | `/admin/texts`       | `GET`, `POST`, `GET /{id}`, `PUT /{id}`, `DELETE /{id}`           |
| **Quiz**       | `/admin/quizzes`     | `GET`, `POST`, `GET /{id}`, `PUT /{id}`, `DELETE /{id}`           |
| **Assignment** | `/admin/assignments` | `GET`, `POST`, `GET /{id}`, `PUT /{id}`, `DELETE /{id}`           |
| **Video**      | `/admin/videos`      | `GET`, `POST`, `GET /{id}`, `PUT /{id}`, `DELETE /{id}`           |
| **Practice**   | `/admin/practices`   | `GET`, `POST`, `GET /{id}`, `PUT /{id}`, `DELETE /{id}`           |
| **File**       | `/admin/files`       | `GET`, `POST`, `GET /{id}`, `POST /{id}` (update), `DELETE /{id}` |

---

## 3. Manajemen Pendaftaran & Peserta

| Method | Endpoint                                      | Deskripsi                                      |
| :----- | :-------------------------------------------- | :--------------------------------------------- |
| `GET`  | `/course-registrant`                          | Melihat semua pendaftar kursus.                |
| `GET`  | `/course-registrant/{registrationId}`         | Melihat detail pendaftaran seorang peserta.    |
| `POST` | `/course-registrant/approve/{registrationId}` | Menyetujui pendaftaran dan membuat enrollment. |

---

## 4. Manajemen Trainer

| Method   | Endpoint                             | Deskripsi                             |
| :------- | :----------------------------------- | :------------------------------------ |
| `POST`   | `/trainers`                          | Membuat data trainer baru.            |
| `PUT`    | `/trainers/{trainer}`                | Memperbarui data trainer.             |
| `DELETE` | `/trainers/{trainer}`                | Menghapus data trainer.               |
| `PUT`    | `/trainers/{trainer}/toggle-starred` | Mengubah status favorit pada trainer. |

---

## 5. Manajemen Umum

### 5.1. FAQ

| Method   | Endpoint      | Deskripsi                       |
| :------- | :------------ | :------------------------------ |
| `POST`   | `/faqs`       | Membuat FAQ baru.               |
| `PUT`    | `/faqs/{faq}` | Memperbarui FAQ yang sudah ada. |
| `DELETE` | `/faqs/{faq}` | Menghapus FAQ.                  |

### 5.2. Zoom Link

| Method   | Endpoint          | Deskripsi                               |
| :------- | :---------------- | :-------------------------------------- |
| `POST`   | `/zoom-link`      | Menambah link Zoom untuk sebuah kursus. |
| `PATCH`  | `/zoom-link/{id}` | Memperbarui link Zoom.                  |
| `DELETE` | `/zoom-link/{id}` | Menghapus link Zoom.                    |

### 5.3. Course Benefits

| Method   | Endpoint                | Deskripsi                                  |
| :------- | :---------------------- | :----------------------------------------- |
| `GET`    | `/course_benefits`      | Mendapatkan semua benefit kursus.          |
| `POST`   | `/course_benefits`      | Menambah benefit baru untuk sebuah kursus. |
| `GET`    | `/course_benefits/{id}` | Detail satu benefit kursus.                |
| `PUT`    | `/course_benefits/{id}` | Memperbarui benefit kursus.                |
| `DELETE` | `/course_benefits/{id}` | Menghapus benefit kursus.                  |

### 5.4. Contact Messages

| Method   | Endpoint              | Deskripsi                          |
| :------- | :-------------------- | :--------------------------------- |
| `DELETE` | `/contacts/{contact}` | Menghapus pesan kontak yang masuk. |

---

## 6. Manajemen Legacy (Struktur Lama)

Endpoint ini terkait dengan struktur data lama dan mungkin akan dihapus di masa mendatang.

### 6.1. Module Concepts & Exercises

| Tipe Konten   | Endpoint            | Deskripsi                 |
| :------------ | :------------------ | :------------------------ |
| **Concepts**  | `/module-concepts`  | CRUD untuk Konsep Modul.  |
| **Exercises** | `/module-exercises` | CRUD untuk Latihan Modul. |
