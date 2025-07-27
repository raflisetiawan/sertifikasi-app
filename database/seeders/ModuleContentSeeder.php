<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\Video;
use App\Models\Text;
use App\Models\File;
use App\Models\Quiz;
use App\Models\Assignment;
use App\Models\Practice;
use Illuminate\Database\Seeder;

class ModuleContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modules = Module::all();
        $allModuleContentsData = $this->getModuleSpecificContentsData();

        if ($modules->isEmpty()) {
            $this->command->info('No modules found, please seed modules first.');
            return;
        }

        foreach ($modules as $module) {
            $this->command->info("Seeding content for module: {$module->title} (ID: {$module->id})");

            $courseName = $module->course->name; // Assuming module has a relationship to course
            $moduleContentsData = $allModuleContentsData[$courseName][$module->title] ?? [];

            foreach ($moduleContentsData as $order => $contentData) {
                $contentType = $contentData['type'];
                $content = null;
                $minimumDuration = null;
                $completionRules = null;

                switch ($contentType) {
                    case 'video':
                        $content = Video::factory()->create([
                            'title' => $contentData['title'],
                            'description' => $contentData['description'] ?? null,
                            'video_url' => $contentData['video_url'] ?? 'https://www.youtube.com/embed/dQw4w9WgXcQ', // Default placeholder
                            'provider' => $contentData['provider'] ?? 'youtube',
                            'video_id' => $contentData['video_id'] ?? 'dQw4w9WgXcQ', // Default placeholder
                            'duration_seconds' => $contentData['duration'],
                            'thumbnail_url' => $contentData['thumbnail_url'] ?? null,
                            'is_downloadable' => $contentData['is_downloadable'] ?? false,
                            'captions' => $contentData['captions'] ?? [],
                        ]);
                        $minimumDuration = $contentData['duration'];
                        break;
                    case 'text':
                        $content = Text::factory()->create([
                            'title' => $contentData['title'],
                            'content' => $contentData['body'], // Map 'body' from data to 'content' in model
                            'format' => $contentData['format'] ?? 'markdown', // Default to markdown
                        ]);
                        break;
                    case 'file':
                        $content = File::factory()->create([
                            'title' => $contentData['title'],
                            'description' => $contentData['description'],
                            'file_path' => 'files/' . ($contentData['file_name'] ?? 'placeholder.pdf'), // Use a consistent path structure
                            'file_name' => $contentData['file_name'],
                            'mime_type' => $contentData['mime_type'],
                            'file_size' => rand(100, 5000), // Placeholder size in KB
                        ]);
                        break;
                    case 'quiz':
                        $content = Quiz::factory()->create([
                            'title' => $contentData['title'],
                            'description' => $contentData['description'] ?? null,
                            'time_limit_minutes' => $contentData['time_limit_minutes'] ?? null,
                            'passing_score' => $contentData['passing_score'] ?? 70,
                            'max_attempts' => $contentData['max_attempts'] ?? 3,
                            'questions' => $contentData['questions'],
                        ]);
                        $completionRules = json_encode(['min_score' => $contentData['passing_score'] ?? 70]);
                        break;
                    case 'assignment':
                        $content = Assignment::factory()->create([
                            'title' => $contentData['title'],
                            'description' => $contentData['description'],
                            'instructions' => $contentData['instructions'] ?? 'Tidak ada instruksi tambahan.',
                            'submission_requirements' => ['format' => 'file', 'content' => 'Unggah file tugas Anda.'],
                            'due_date' => now()->addDays(rand(7, 21)),
                            'max_file_size_mb' => $contentData['max_file_size_mb'] ?? 10,
                            'allowed_file_types' => $contentData['allowed_file_types'] ?? ['pdf', 'zip', 'docx'],
                        ]);
                        break;
                    case 'practice':
                        $content = Practice::factory()->create([
                            'title' => $contentData['title'],
                            'description' => $contentData['description'] ?? null,
                            'time_limit_minutes' => $contentData['time_limit_minutes'] ?? null,
                            'questions' => $contentData['questions'],
                        ]);
                        break;
                    default:
                        throw new \InvalidArgumentException("Unknown content type: {$contentType}");
                }

                ModuleContent::factory()->create([
                    'module_id' => $module->id,
                    'title' => $contentData['title'],
                    'content_type' => $contentType,
                    'content_id' => $content->id,
                    'order' => $order + 1,
                    'is_required' => $contentData['is_required'] ?? true, // Default to required
                    'minimum_duration_seconds' => $minimumDuration,
                    'completion_rules' => $completionRules,
                ]);
            }
        }
    }

    private function generateContentTitle(string $type, $content)
    {
        switch ($type) {
            case 'video':
                return 'Video: ' . $content->title;
            case 'text':
                return 'Materi Bacaan: ' . $content->title;
            case 'file':
                return 'Unduh Dokumen: ' . $content->title;
            case 'quiz':
                return 'Kuis: ' . $content->title;
            case 'assignment':
                return 'Tugas: ' . $content->title;
            case 'practice':
                return 'Latihan: ' . $content->title;
            default:
                return $content->title;
        }
    }

    private function getModuleSpecificContentsData(): array
    {
        return [
            'Pengembangan Web Full-Stack' => [
                'Pengantar Pengembangan Web' => [
                    ['type' => 'video', 'title' => 'Sejarah dan Evolusi Web', 'description' => 'Video pengantar tentang sejarah dan perkembangan World Wide Web.', 'duration' => 300, 'provider' => 'youtube', 'video_id' => 'dQw4w9WgXcQ', 'thumbnail_url' => 'https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg', 'is_downloadable' => false, 'captions' => []],
                    ['type' => 'text', 'title' => 'Cara Kerja Internet dan Protokol Dasar', 'body' => 'Penjelasan mendalam tentang HTTP, TCP/IP, DNS, dan arsitektur client-server.', 'format' => 'markdown'],
                    ['type' => 'quiz', 'title' => 'Kuis Pengantar Web', 'description' => 'Uji pemahaman Anda tentang dasar-dasar pengembangan web.', 'time_limit_minutes' => 10, 'passing_score' => 70, 'max_attempts' => 3, 'questions' => [
                        ['question' => 'Apa kepanjangan dari HTML?', 'options' => ['Hyper Text Markup Language', 'High-level Text Machine Language', 'Hyperlink and Text Markup Language'], 'correct_answer' => 'Hyper Text Markup Language', 'explanation' => 'HTML adalah bahasa standar untuk membuat halaman web.'],
                        ['question' => 'Apa fungsi utama CSS?', 'options' => ['Untuk membuat halaman web interaktif', 'Untuk mendefinisikan struktur halaman web', 'Untuk mengatur gaya dan tampilan halaman web'], 'correct_answer' => 'Untuk mengatur gaya dan tampilan halaman web', 'explanation' => 'CSS digunakan untuk styling elemen HTML.'],
                        ['question' => 'Bahasa pemrograman apa yang berjalan di sisi klien?', 'options' => ['PHP', 'Python', 'JavaScript'], 'correct_answer' => 'JavaScript', 'explanation' => 'JavaScript adalah bahasa scripting yang memungkinkan Anda mengimplementasikan fitur kompleks pada halaman web.'],
                    ]],
                ],
                'Dasar-dasar Front-End (HTML, CSS, JS)' => [
                    ['type' => 'text', 'title' => 'Struktur Dokumen HTML5', 'body' => 'Mempelajari elemen dasar HTML, semantic HTML, dan struktur dokumen yang baik.', 'format' => 'markdown'],
                    ['type' => 'video', 'title' => 'Styling dengan CSS3 dan Responsif Desain', 'duration' => 450],
                    ['type' => 'practice', 'title' => 'Latihan Membuat Layout Dasar Responsif', 'description' => 'Praktik membuat layout web yang adaptif untuk berbagai ukuran layar.', 'time_limit_minutes' => 30, 'questions' => [
                        ['question' => 'Buatlah struktur HTML dasar untuk halaman web dengan judul "Latihan Layout".', 'type' => 'code', 'answer_key' => '<!DOCTYPE html>\n<html>\n<head>\n    <title>Latihan Layout</title>\n</head>\n<body>\n    <header>\n        <h1>Judul Utama</h1>\n    </header>\n    <main>\n        <p>Konten utama.</p>\n    </main>\n    <footer>\n        <p>Hak Cipta 2025</p>\n    </footer>\n</body>\n</html>', 'explanation' => 'Struktur ini menggunakan elemen semantik seperti <header>, <main>, dan <footer>.'],
                        ['question' => 'Gunakan CSS untuk membuat lebar <main> menjadi 80% dari lebar viewport dan berada di tengah.', 'type' => 'code', 'answer_key' => 'main { width: 80%; margin: 0 auto; }', 'explanation' => '`width: 80%` mengatur lebar, dan `margin: 0 auto` membuatnya berada di tengah secara horizontal.'],
                        ['question' => 'Jelaskan secara singkat apa fungsi dari `box-sizing: border-box;` dalam CSS.', 'type' => 'short_answer', 'answer_key' => 'Memastikan bahwa padding dan border suatu elemen termasuk dalam total lebar dan tinggi elemen tersebut.', 'explanation' => 'Ini membuat kalkulasi layout menjadi lebih intuitif dan mudah dikelola.'],
                    ]],
                    [
                        'type' => 'file',
                        'title' => 'Cheat Sheet HTML & CSS',
                        'description' => 'Ringkasan cepat properti HTML dan CSS yang paling sering digunakan. Sangat berguna untuk referensi saat coding.',
                        'file_name' => 'html_css_cheatsheet.pdf',
                        'mime_type' => 'application/pdf',
                    ],
                ],
                'Pengenalan Back-End (PHP & Laravel)' => [
                    ['type' => 'video', 'title' => 'Konsep Dasar PHP dan Laravel', 'duration' => 600],
                    ['type' => 'text', 'title' => 'Routing, Controller, dan Views di Laravel', 'body' => 'Memahami alur request-response di Laravel.', 'format' => 'markdown'],
                    [
                        'type' => 'assignment',
                        'title' => 'Membuat CRUD Sederhana dengan Laravel',
                        'description' => 'Tugas untuk membangun aplikasi Create, Read, Update, Delete (CRUD) dasar untuk manajemen data (misalnya, daftar tugas atau postingan blog).',
                        'instructions' => "1. Buat model, migration, controller, dan route baru untuk entitas yang Anda pilih (contoh: Task).\n2. Implementasikan semua fungsi CRUD: menampilkan daftar, menambah data baru, mengedit data, dan menghapus data.\n3. Buat view sederhana menggunakan Blade untuk setiap halaman (index, create, edit).\n4. Terapkan validasi input di controller saat menyimpan atau memperbarui data.",
                        'submission_requirements' => ['format' => 'zip', 'content' => 'Unggah seluruh source code proyek Laravel Anda dalam format .zip.'],
                        'max_file_size_mb' => 15,
                        'allowed_file_types' => ['zip', 'rar'],
                    ],
                    ['type' => 'quiz', 'title' => 'Kuis Dasar Back-End', 'description' => 'Uji pemahaman Anda tentang konsep dasar PHP dan Laravel.', 'time_limit_minutes' => 15, 'passing_score' => 75, 'max_attempts' => 2, 'questions' => [
                        ['question' => 'Apa fungsi utama dari Laravel?', 'options' => ['Framework PHP untuk pengembangan web', 'Bahasa pemrograman front-end', 'Database management system'], 'correct_answer' => 'Framework PHP untuk pengembangan web', 'explanation' => 'Laravel adalah framework PHP yang populer untuk membangun aplikasi web.'],
                        ['question' => 'Apa itu Eloquent ORM di Laravel?', 'options' => ['Sistem routing', 'Object-Relational Mapper', 'Template engine'], 'correct_answer' => 'Object-Relational Mapper', 'explanation' => 'Eloquent adalah ORM bawaan Laravel untuk berinteraksi dengan database.'],
                        ['question' => 'Bagaimana cara mendefinisikan route di Laravel?', 'options' => ['Menggunakan method Route::get() atau Route::post()', 'Menggunakan fungsi define_route()', 'Melalui file config/routes.php'], 'correct_answer' => 'Menggunakan method Route::get() atau Route::post()', 'explanation' => 'Route didefinisikan di file web.php atau api.php menggunakan facade Route.'],
                    ]],
                ],
                'Integrasi Front-End & Back-End' => [
                    ['type' => 'video', 'title' => 'Membangun RESTful API dengan Laravel', 'duration' => 500],
                    ['type' => 'text', 'title' => 'Mengonsumsi API dengan JavaScript (Fetch API/Axios)', 'body' => 'Cara melakukan request HTTP dari sisi client.', 'format' => 'markdown'],
                    ['type' => 'practice', 'title' => 'Latihan Integrasi API', 'description' => 'Praktik menghubungkan front-end dengan API yang telah dibuat.', 'time_limit_minutes' => 45, 'questions' => [
                        ['question' => 'Tuliskan kode JavaScript menggunakan Fetch API untuk melakukan GET request ke endpoint `/api/products`.', 'type' => 'code', 'answer_key' => 'fetch(\'/api/products\')\n    .then(response => response.json())\n    .then(data => console.log(data))\n    .catch(error => console.error(\'Error:\', error));', 'explanation' => 'Kode ini mengambil data dari API, mengubah respons menjadi JSON, lalu menampilkannya di konsol.'],
                        ['question' => 'Apa perbedaan utama antara metode HTTP POST dan PUT?', 'type' => 'short_answer', 'answer_key' => 'POST digunakan untuk membuat sumber daya baru, sedangkan PUT digunakan untuk memperbarui sumber daya yang sudah ada secara keseluruhan (mengganti).', 'explanation' => 'POST bersifat tidak idempoten, sementara PUT bersifat idempoten.'],
                    ]],
                ],
                'Deployment & Skalabilitas' => [
                    ['type' => 'text', 'title' => 'Persiapan Aplikasi untuk Produksi', 'body' => 'Optimasi kode, konfigurasi lingkungan produksi.', 'format' => 'markdown'],
                    ['type' => 'video', 'title' => 'Strategi Deployment Aplikasi Web', 'duration' => 400],
                    [
                        'type' => 'file',
                        'title' => 'Checklist Deployment Aplikasi',
                        'description' => 'Daftar periksa langkah-langkah penting yang harus dilakukan sebelum dan sesudah mendeploy aplikasi web ke server produksi.',
                        'file_name' => 'deployment_checklist.pdf',
                        'mime_type' => 'application/pdf',
                    ],
                ],
            ],
            'Sains Data dengan Python' => [
                'Pengantar Sains Data & Python' => [
                    ['type' => 'video', 'title' => 'Apa itu Sains Data dan Mengapa Python?', 'duration' => 350],
                    ['type' => 'text', 'title' => 'Instalasi Lingkungan Python dan Jupyter Notebook', 'body' => 'Panduan langkah demi langkah untuk menyiapkan lingkungan kerja.', 'format' => 'markdown'],
                    ['type' => 'practice', 'title' => 'Latihan Dasar Python untuk Data', 'description' => 'Praktik sintaks dasar Python yang relevan untuk sains data.', 'time_limit_minutes' => 25, 'questions' => [
                        ['question' => 'Buat sebuah list Python berisi 5 nama buah.', 'type' => 'code', 'answer_key' => 'fruits = ["apel", "pisang", "mangga", "jeruk", "anggur"]', 'explanation' => 'List adalah struktur data dasar di Python untuk menyimpan koleksi item.'],
                        ['question' => 'Bagaimana cara mengakses elemen ketiga dari list `fruits` yang telah Anda buat?', 'type' => 'code', 'answer_key' => 'fruits[2]', 'explanation' => 'Indeks di Python dimulai dari 0, jadi elemen ketiga memiliki indeks 2.'],
                        ['question' => 'Apa output dari kode berikut: `len("Sains Data")`?', 'type' => 'multiple_choice', 'options' => ['9', '10', '11'], 'answer_key' => '10', 'explanation' => 'Fungsi `len()` menghitung jumlah karakter, termasuk spasi.'],
                    ]],
                ],
                'Analisis Data dengan Pandas' => [
                    ['type' => 'video', 'title' => 'Pengenalan Pandas DataFrame', 'duration' => 400],
                    ['type' => 'text', 'title' => 'Membersihkan dan Memanipulasi Data dengan Pandas', 'body' => 'Teknik handling missing values, filtering, grouping, dan merging data.', 'format' => 'markdown'],
                    [
                        'type' => 'assignment',
                        'title' => 'Analisis Dataset Sederhana dengan Pandas',
                        'description' => 'Lakukan analisis data eksplorasi (EDA) pada dataset yang disediakan (misalnya, dataset Titanic atau Iris). Temukan insight menarik dari data tersebut.',
                        'instructions' => "1. Muat dataset menggunakan Pandas.\n2. Lakukan pembersihan data (tangani nilai yang hilang, perbaiki tipe data jika perlu).\n3. Hitung statistik deskriptif untuk kolom-kolom numerik.\n4. Buat setidaknya 3 visualisasi data yang berbeda (contoh: histogram, bar chart, scatter plot) untuk menyoroti temuan Anda.\n5. Tulis ringkasan singkat (1-2 paragraf) tentang insight utama yang Anda temukan dari analisis.",
                        'submission_requirements' => ['format' => 'jupyter', 'content' => 'File Jupyter Notebook (.ipynb) yang berisi kode, visualisasi, dan analisis Anda.'],
                        'max_file_size_mb' => 5,
                        'allowed_file_types' => ['ipynb'],
                    ],
                ],
                'Visualisasi Data dengan Matplotlib & Seaborn' => [
                    ['type' => 'video', 'title' => 'Membuat Plot Dasar dengan Matplotlib', 'duration' => 380],
                    ['type' => 'text', 'title' => 'Visualisasi Statistik dengan Seaborn', 'body' => 'Membuat visualisasi yang lebih kompleks dan estetis.', 'format' => 'markdown'],
                    ['type' => 'practice', 'title' => 'Latihan Membuat Infografis Data', 'description' => 'Praktik membuat visualisasi data yang informatif.', 'time_limit_minutes' => 35, 'questions' => [
                        ['question' => 'Sebutkan 3 jenis plot yang bisa dibuat menggunakan Matplotlib atau Seaborn.', 'type' => 'short_answer', 'answer_key' => 'Contoh: Line plot, bar chart, scatter plot, histogram, box plot.', 'explanation' => 'Ada banyak jenis plot untuk memvisualisasikan hubungan dan distribusi data.'],
                        ['question' => 'Kapan Anda akan menggunakan scatter plot?', 'type' => 'short_answer', 'answer_key' => 'Untuk mengamati hubungan atau korelasi antara dua variabel numerik.', 'explanation' => 'Scatter plot sangat baik untuk melihat pola antara dua set data.'],
                    ]],
                ],
                'Machine Learning Dasar dengan Scikit-learn' => [
                    ['type' => 'video', 'title' => 'Pengantar Machine Learning dan Jenis-jenisnya', 'duration' => 420],
                    ['type' => 'text', 'title' => 'Algoritma Klasifikasi dan Regresi', 'body' => 'Penjelasan tentang Linear Regression, Logistic Regression, Decision Trees.', 'format' => 'markdown'],
                    ['type' => 'quiz', 'title' => 'Kuis Konsep Machine Learning', 'description' => 'Uji pemahaman Anda tentang konsep dasar Machine Learning.', 'time_limit_minutes' => 20, 'passing_score' => 80, 'max_attempts' => 2, 'questions' => [
                        ['question' => 'Apa perbedaan utama antara supervised dan unsupervised learning?', 'options' => ['Supervised learning menggunakan data berlabel, unsupervised tidak.', 'Supervised learning untuk klasifikasi, unsupervised untuk regresi.', 'Tidak ada perbedaan signifikan.'], 'correct_answer' => 'Supervised learning menggunakan data berlabel, unsupervised tidak.', 'explanation' => 'Supervised learning belajar dari data yang sudah memiliki label output, sedangkan unsupervised learning mencari pola dalam data tanpa label.'],
                        ['question' => 'Algoritma mana yang cocok untuk masalah klasifikasi biner?', 'options' => ['Linear Regression', 'K-Means Clustering', 'Logistic Regression'], 'correct_answer' => 'Logistic Regression', 'explanation' => 'Logistic Regression adalah algoritma klasifikasi yang umum digunakan untuk masalah biner.'],
                        ['question' => 'Apa itu overfitting dalam Machine Learning?', 'options' => ['Model terlalu sederhana untuk data', 'Model terlalu kompleks dan cocok dengan noise dalam data', 'Model tidak dapat belajar dari data'], 'correct_answer' => 'Model terlalu kompleks dan cocok dengan noise dalam data', 'explanation' => 'Overfitting terjadi ketika model belajar terlalu banyak dari data pelatihan, termasuk noise, sehingga performanya buruk pada data baru.'],
                    ]],
                    [
                        'type' => 'assignment',
                        'title' => 'Membangun Model Prediktif Pertama Anda',
                        'description' => 'Bangun dan evaluasi model machine learning untuk memprediksi target variabel pada dataset yang diberikan.',
                        'instructions' => "1. Pilih dataset klasifikasi atau regresi dari sumber seperti Kaggle atau UCI Machine Learning Repository.\n2. Bagi data menjadi set pelatihan (80%) dan pengujian (20%).\n3. Latih setidaknya 2 model machine learning yang berbeda (misalnya, Logistic Regression dan K-Nearest Neighbors untuk klasifikasi; atau Linear Regression dan Decision Tree Regressor untuk regresi).\n4. Evaluasi performa model menggunakan metrik yang sesuai (misalnya, akurasi, presisi, recall untuk klasifikasi; MSE, R-squared untuk regresi).\n5. Bandingkan performa kedua model dan berikan kesimpulan model mana yang lebih baik untuk dataset ini.",
                        'submission_requirements' => ['format' => 'jupyter', 'content' => 'File Jupyter Notebook (.ipynb) yang berisi kode, hasil evaluasi, dan analisis Anda.'],
                        'max_file_size_mb' => 5,
                        'allowed_file_types' => ['ipynb'],
                    ],
                ],
                'Proyek Akhir Sains Data' => [
                    ['type' => 'text', 'title' => 'Panduan Proyek Sains Data End-to-End', 'body' => 'Langkah-langkah dari perumusan masalah hingga deployment model.', 'format' => 'markdown'],
                    [
                        'type' => 'file',
                        'title' => 'Template Laporan Proyek Sains Data',
                        'description' => 'Gunakan template ini untuk menyusun laporan akhir proyek sains data Anda secara terstruktur dan profesional.',
                        'file_name' => 'project_report_template.docx',
                        'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ],
                    [
                        'type' => 'assignment',
                        'title' => 'Presentasi Proyek Akhir',
                        'description' => 'Presentasikan hasil proyek sains data Anda dalam format yang ringkas dan jelas.',
                        'instructions' => "1. Buat sebuah presentasi (maksimal 10 slide) yang merangkum proyek Anda.\n2. Jelaskan masalah bisnis, data yang digunakan, metodologi, hasil, dan kesimpulan.\n3. Fokus pada visualisasi data dan insight yang dapat ditindaklanjuti.\n4. Siapkan demo singkat jika memungkinkan.",
                        'submission_requirements' => ['format' => 'presentation', 'content' => 'Unggah file presentasi Anda (PPTX atau PDF).'],
                        'max_file_size_mb' => 20,
                        'allowed_file_types' => ['pptx', 'pdf'],
                    ],
                ],
            ],
            'Masterclass Pemasaran Digital' => [
                'Strategi Pemasaran Digital' => [
                    ['type' => 'video', 'title' => 'Memahami Ekosistem Pemasaran Digital', 'duration' => 300],
                    ['type' => 'text', 'title' => 'Menyusun Persona Pembeli dan Customer Journey', 'body' => 'Identifikasi target audiens dan alur mereka.', 'format' => 'markdown'],
                    ['type' => 'quiz', 'title' => 'Kuis Strategi Pemasaran', 'description' => 'Uji pemahaman Anda tentang dasar-dasar strategi pemasaran digital.', 'time_limit_minutes' => 10, 'passing_score' => 70, 'max_attempts' => 3, 'questions' => [
                        ['question' => 'Apa itu SEO?', 'options' => ['Search Engine Optimization', 'Social Engagement Optimization', 'Sales Event Organization'], 'correct_answer' => 'Search Engine Optimization', 'explanation' => 'SEO adalah praktik meningkatkan kualitas dan kuantitas lalu lintas situs web ke situs web atau halaman web dari mesin pencari.'],
                        ['question' => 'Platform media sosial mana yang paling cocok untuk B2B marketing?', 'options' => ['TikTok', 'Instagram', 'LinkedIn'], 'correct_answer' => 'LinkedIn', 'explanation' => 'LinkedIn adalah platform profesional yang sangat efektif untuk pemasaran B2B.'],
                        ['question' => 'Apa metrik utama untuk mengukur keberhasilan email marketing?', 'options' => ['Click-Through Rate (CTR)', 'Cost Per Click (CPC)', 'Return on Investment (ROI)'], 'correct_answer' => 'Click-Through Rate (CTR)', 'explanation' => 'CTR adalah persentase orang yang mengklik tautan dalam email Anda.'],
                    ]],
                ],
                'SEO & SEM' => [
                    ['type' => 'video', 'title' => 'Dasar-dasar SEO On-Page dan Off-Page', 'duration' => 400],
                    ['type' => 'text', 'title' => 'Riset Kata Kunci dan Analisis Kompetitor', 'body' => 'Teknik menemukan kata kunci yang efektif.', 'format' => 'markdown'],
                    ['type' => 'practice', 'title' => 'Latihan Optimasi SEO Halaman Web', 'description' => 'Praktik menerapkan optimasi SEO dasar.', 'time_limit_minutes' => 20, 'questions' => [
                        ['question' => 'Sebutkan 3 elemen on-page SEO yang penting.', 'type' => 'short_answer', 'answer_key' => 'Contoh: Title tag, meta description, header tags (H1, H2), URL structure, alt text gambar.', 'explanation' => 'Elemen-elemen ini membantu mesin pencari memahami konten halaman Anda.'],
                        ['question' => 'Mengapa kecepatan loading halaman penting untuk SEO?', 'type' => 'short_answer', 'answer_key' => 'Karena Google menggunakannya sebagai faktor peringkat dan juga mempengaruhi pengalaman pengguna (user experience).', 'explanation' => 'Halaman yang lambat dapat meningkatkan bounce rate dan menurunkan peringkat.'],
                    ]],
                ],
                'Pemasaran Media Sosial' => [
                    ['type' => 'video', 'title' => 'Strategi Konten untuk Berbagai Platform Media Sosial', 'duration' => 350],
                    ['type' => 'text', 'title' => 'Manajemen Komunitas dan Engagement', 'body' => 'Membangun interaksi yang kuat dengan audiens.', 'format' => 'markdown'],
                    [
                        'type' => 'assignment',
                        'title' => 'Membuat Kalender Konten Media Sosial',
                        'description' => 'Rencanakan kalender konten media sosial untuk sebuah brand (pilih brand fiktif atau nyata) selama satu minggu.',
                        'instructions' => "1. Tentukan tujuan, target audiens, dan platform media sosial utama (pilih 2 platform, misal: Instagram dan TikTok).\n2. Buat jadwal posting untuk 7 hari ke depan, termasuk waktu posting.\n3. Untuk setiap postingan, tentukan: format konten (gambar, video, stories, dll.), caption/copywriting, dan hashtag yang relevan.\n4. Pastikan ada variasi pilar konten (contoh: 40% edukasi, 30% hiburan, 20% promosi, 10% interaksi).",
                        'submission_requirements' => ['format' => 'spreadsheet', 'content' => 'File spreadsheet (Excel/Google Sheets) atau dokumen PDF yang berisi kalender konten Anda.'],
                        'max_file_size_mb' => 2,
                        'allowed_file_types' => ['pdf', 'xlsx', 'csv'],
                    ],
                ],
                'Email Marketing & Otomatisasi' => [
                    ['type' => 'video', 'title' => 'Membangun Daftar Email dan Segmentasi', 'duration' => 300],
                    ['type' => 'text', 'title' => 'Merancang Kampanye Email yang Efektif', 'body' => 'Tips untuk subject lines, body content, dan CTA.', 'format' => 'markdown'],
                    ['type' => 'practice', 'title' => 'Latihan Membuat Alur Otomatisasi Email', 'description' => 'Praktik membuat alur email otomatis.', 'time_limit_minutes' => 30, 'questions' => [
                        ['question' => 'Buatlah sebuah judul (subject line) email yang menarik untuk promosi diskon 50%.', 'type' => 'short_answer', 'answer_key' => 'Contoh: "Diskon 50% Berakhir Malam Ini! 😱" atau "Hemat Setengah Harga untuk Produk Favoritmu!"', 'explanation' => 'Judul yang baik harus menciptakan urgensi atau menyoroti manfaat utama.'],
                        ['question' => 'Jelaskan alur email otomatis sederhana untuk menyambut pelanggan baru.', 'type' => 'short_answer', 'answer_key' => '1. Kirim email selamat datang segera setelah mendaftar. 2. Kirim email perkenalan produk/fitur 2 hari kemudian. 3. Kirim email penawaran khusus 5 hari kemudian.', 'explanation' => 'Alur otomatis membantu membangun hubungan yang kuat dengan pelanggan secara bertahap.'],
                    ]],
                ],
                'Analisis & Pelaporan Pemasaran' => [
                    ['type' => 'video', 'title' => 'Penggunaan Google Analytics untuk Pemasaran', 'duration' => 450],
                    ['type' => 'text', 'title' => 'Memahami Metrik Kunci dan Membuat Laporan', 'body' => 'Interpretasi data dan penyusunan laporan yang informatif.', 'format' => 'markdown'],
                    [
                        'type' => 'assignment',
                        'title' => 'Analisis Performa Kampanye Pemasaran',
                        'description' => 'Analisis data fiktif dari sebuah kampanye pemasaran digital dan buat laporan singkat.',
                        'instructions' => "1. Gunakan data yang disediakan (file CSV).\n2. Hitung metrik kunci seperti CTR (Click-Through Rate), CPC (Cost Per Click), dan CPA (Cost Per Acquisition).\n3. Buat setidaknya 2 visualisasi untuk menampilkan performa kampanye.\n4. Berikan rekomendasi berdasarkan analisis Anda: apa yang berhasil, apa yang tidak, dan apa yang harus dilakukan selanjutnya.",
                        'submission_requirements' => ['format' => 'document', 'content' => 'Unggah laporan Anda dalam format PDF atau DOCX.'],
                        'max_file_size_mb' => 5,
                        'allowed_file_types' => ['pdf', 'docx'],
                    ],
                ],
            ],
            'Dasar-dasar Desain UI/UX' => [
                'Pengantar UI/UX & Riset Pengguna' => [
                    ['type' => 'video', 'title' => 'Memahami Peran UI/UX dalam Produk Digital', 'duration' => 300],
                    ['type' => 'text', 'title' => 'Metode Riset Pengguna: Wawancara dan Survei', 'body' => 'Teknik mengumpulkan insight dari pengguna.', 'format' => 'markdown'],
                    ['type' => 'quiz', 'title' => 'Kuis Pengantar UI/UX', 'description' => 'Uji pemahaman Anda tentang dasar-dasar UI/UX dan riset pengguna.', 'time_limit_minutes' => 10, 'passing_score' => 70, 'max_attempts' => 3, 'questions' => [
                        ['question' => 'Apa perbedaan utama antara UI dan UX?', 'options' => ['UI adalah User Interface, UX adalah User Experience', 'UI fokus pada tampilan, UX fokus pada perasaan pengguna', 'Keduanya sama saja'], 'correct_answer' => 'UI fokus pada tampilan, UX fokus pada perasaan pengguna', 'explanation' => 'UI adalah antarmuka pengguna, sedangkan UX adalah pengalaman pengguna secara keseluruhan.'],
                        ['question' => 'Mengapa riset pengguna penting dalam desain UI/UX?', 'options' => ['Untuk membuat desain yang lebih cepat', 'Untuk memahami kebutuhan dan perilaku pengguna', 'Untuk mengurangi biaya pengembangan'], 'correct_answer' => 'Untuk memahami kebutuhan dan perilaku pengguna', 'explanation' => 'Riset pengguna membantu desainer membuat produk yang relevan dan berguna bagi target audiens.'],
                        ['question' => 'Apa itu wireframe?', 'options' => ['Desain akhir aplikasi', 'Sketsa kasar struktur halaman', 'Prototipe interaktif'], 'correct_answer' => 'Sketsa kasar struktur halaman', 'explanation' => 'Wireframe adalah representasi visual dasar dari tata letak halaman web atau aplikasi.'],
                    ]],
                ],
                'Wireframing & Prototyping' => [
                    ['type' => 'video', 'title' => 'Membuat Wireframe dengan Figma', 'duration' => 400],
                    ['type' => 'text', 'title' => 'Dari Wireframe ke Prototipe Interaktif', 'body' => 'Langkah-langkah mengubah sketsa menjadi prototipe.', 'format' => 'markdown'],
                    ['type' => 'practice', 'title' => 'Latihan Membuat Prototipe Aplikasi Sederhana', 'description' => 'Praktik membuat prototipe interaktif.', 'time_limit_minutes' => 45, 'questions' => [
                        ['question' => 'Sebutkan 3 alat (tools) populer untuk membuat wireframe dan prototipe.', 'type' => 'short_answer', 'answer_key' => 'Contoh: Figma, Sketch, Adobe XD, Balsamiq.', 'explanation' => 'Alat-alat ini sangat umum digunakan di industri desain UI/UX.'],
                        ['question' => 'Apa tujuan utama membuat prototipe interaktif?', 'type' => 'short_answer', 'answer_key' => 'Untuk menguji alur pengguna (user flow) dan mendapatkan umpan balik sebelum produk dikembangkan secara penuh.', 'explanation' => 'Prototipe membantu memvalidasi ide desain dengan biaya yang lebih rendah.'],
                    ]],
                ],
                'Prinsip Desain Visual & Interaksi' => [
                    ['type' => 'video', 'title' => 'Teori Warna dan Tipografi dalam Desain UI', 'duration' => 350],
                    ['type' => 'text', 'title' => 'Hukum Gestalt dan Hierarki Visual', 'body' => 'Memahami bagaimana mata manusia memproses informasi visual.', 'format' => 'markdown'],
                    [
                        'type' => 'assignment',
                        'title' => 'Redesain Antarmuka Aplikasi',
                        'description' => 'Pilih satu halaman dari aplikasi atau situs web populer yang menurut Anda dapat ditingkatkan. Lakukan redesain pada halaman tersebut.',
                        'instructions' => "1. Pilih satu halaman (misalnya, halaman login Instagram, halaman produk Tokopedia).\n2. Analisis kelemahan dari desain yang ada.\n3. Buat wireframe atau mockup dari desain baru Anda.\n4. Berikan justifikasi singkat (1-2 paragraf) untuk perubahan desain yang Anda buat, kaitkan dengan prinsip-prinsip desain visual atau UX.",
                        'submission_requirements' => ['format' => 'image/pdf', 'content' => 'Unggah gambar (PNG/JPG) atau PDF dari desain baru Anda beserta justifikasinya.'],
                        'max_file_size_mb' => 10,
                        'allowed_file_types' => ['png', 'jpg', 'pdf'],
                    ],
                ],
                'Usability Testing & Iterasi' => [
                    ['type' => 'video', 'title' => 'Melakukan Usability Testing dan Mengumpulkan Feedback', 'duration' => 300],
                    ['type' => 'text', 'title' => 'Menganalisis Hasil Testing dan Iterasi Desain', 'body' => 'Cara menggunakan feedback untuk perbaikan desain.', 'format' => 'markdown'],
                    ['type' => 'practice', 'title' => 'Latihan Analisis Usability Report', 'description' => 'Praktik menganalisis laporan usability.', 'time_limit_minutes' => 25, 'questions' => [
                        ['question' => 'Anda melihat 4 dari 5 peserta gagal menyelesaikan tugas untuk mengubah foto profil. Apa kesimpulan awal Anda?', 'type' => 'short_answer', 'answer_key' => 'Ada masalah serius pada alur atau desain fitur ubah foto profil yang membuatnya tidak intuitif.', 'explanation' => 'Tingkat kegagalan yang tinggi menunjukkan adanya masalah usability yang signifikan.'],
                    ]],
                ],
                'Portofolio Desain UI/UX' => [
                    ['type' => 'text', 'title' => 'Membangun Studi Kasus untuk Portofolio', 'body' => 'Panduan menyusun studi kasus yang menarik.', 'format' => 'markdown'],
                    [
                        'type' => 'file',
                        'title' => 'Template Portofolio UI/UX',
                        'description' => 'Template presentasi untuk membantu Anda menyusun portofolio dan studi kasus desain UI/UX secara profesional.',
                        'file_name' => 'ui_ux_portfolio_template.pdf',
                        'mime_type' => 'application/pdf',
                    ],
                    [
                        'type' => 'assignment',
                        'title' => 'Presentasi Portofolio Desain',
                        'description' => 'Buat satu studi kasus lengkap dari salah satu proyek desain yang pernah Anda kerjakan (atau dari tugas di kursus ini) untuk portofolio Anda.',
                        'instructions' => "1. Ikuti struktur studi kasus standar: Latar Belakang, Masalah, Peran Anda, Proses Desain (Riset, Ideasi, Desain, Testing), Solusi Akhir, dan Hasil.\n2. Sertakan visual seperti wireframe, mockup, dan prototipe.\n3. Tulis narasi yang jelas dan ringkas.",
                        'submission_requirements' => ['format' => 'pdf/link', 'content' => 'Unggah studi kasus Anda dalam format PDF atau berikan link ke portofolio online Anda (misal: Behance, Dribbble, atau situs pribadi).'],
                        'max_file_size_mb' => 25,
                        'allowed_file_types' => ['pdf'],
                    ],
                ],
            ],
            'Spesialis Media Sosial' => [
                'Strategi Konten Media Sosial' => [
                    ['type' => 'video', 'title' => 'Mengembangkan Strategi Konten yang Menarik', 'duration' => 300],
                    ['type' => 'text', 'title' => 'Membuat Kalender Konten dan Jadwal Posting', 'body' => 'Perencanaan konten yang sistematis.', 'format' => 'markdown'],
                    ['type' => 'quiz', 'title' => 'Kuis Strategi Konten', 'description' => 'Uji pemahaman Anda tentang strategi konten media sosial.', 'time_limit_minutes' => 10, 'passing_score' => 70, 'max_attempts' => 3, 'questions' => [
                        ['question' => 'Apa tujuan utama dari strategi konten media sosial?', 'options' => ['Meningkatkan jumlah follower', 'Mencapai tujuan bisnis melalui konten yang relevan', 'Membuat konten viral'], 'correct_answer' => 'Mencapai tujuan bisnis melalui konten yang relevan', 'explanation' => 'Strategi konten harus selaras dengan tujuan bisnis.'],
                        ['question' => 'Apa itu kalender konten?', 'options' => ['Daftar ide konten', 'Jadwal publikasi konten', 'Analisis performa konten'], 'correct_answer' => 'Jadwal publikasi konten', 'explanation' => 'Kalender konten membantu merencanakan dan mengatur publikasi konten secara sistematis.'],
                        ['question' => 'Mengapa penting untuk berinteraksi dengan audiens di media sosial?', 'options' => ['Meningkatkan engagement dan loyalitas', 'Hanya untuk mengisi waktu luang', 'Tidak ada manfaat signifikan'], 'correct_answer' => 'Meningkatkan engagement dan loyalitas', 'explanation' => 'Interaksi membangun hubungan yang kuat dengan audiens.'],
                    ]],
                ],
                'Manajemen Komunitas & Engagement' => [
                    ['type' => 'video', 'title' => 'Teknik Meningkatkan Engagement Audiens', 'duration' => 350],
                    ['type' => 'text', 'title' => 'Menangani Komentar dan Krisis di Media Sosial', 'body' => 'Strategi komunikasi yang efektif.', 'format' => 'markdown'],
                    ['type' => 'practice', 'title' => 'Latihan Respon Komentar Negatif', 'description' => 'Praktik merespon komentar yang menantang.', 'time_limit_minutes' => 20, 'questions' => [
                        ['question' => 'Bagaimana Anda merespon komentar: "Produk Anda jelek, saya kecewa!"?', 'type' => 'short_answer', 'answer_key' => '1. Ucapkan terima kasih atas masukannya dan minta maaf atas pengalaman buruknya. 2. Tawarkan solusi atau ajak diskusi lebih lanjut di DM untuk menyelesaikan masalah. 3. Tunjukkan empati dan jangan defensif.', 'explanation' => 'Respon yang baik harus profesional, empatik, dan solutif.'],
                    ]],
                ],
                'Iklan Berbayar di Media Sosial' => [
                    ['type' => 'video', 'title' => 'Membuat Kampanye Iklan Facebook dan Instagram Ads', 'duration' => 400],
                    ['type' => 'text', 'title' => 'Penargetan Audiens dan Optimasi Anggaran Iklan', 'body' => 'Strategi untuk mencapai ROI maksimal.', 'format' => 'markdown'],
                    [
                        'type' => 'assignment',
                        'title' => 'Merancang Kampanye Iklan Media Sosial',
                        'description' => 'Rancang sebuah kampanye iklan berbayar di Facebook/Instagram untuk sebuah produk fiktif.',
                        'instructions' => "1. Tentukan tujuan kampanye (misal: meningkatkan traffic, konversi, atau brand awareness).\n2. Definisikan target audiens Anda (demografi, minat, perilaku).\n3. Buat draf materi iklan (ad creative), termasuk gambar/video dan ad copy.\n4. Tentukan anggaran harian dan durasi kampanye.",
                        'submission_requirements' => ['format' => 'document', 'content' => 'Unggah rencana kampanye Anda dalam format PDF atau DOCX.'],
                        'max_file_size_mb' => 5,
                        'allowed_file_types' => ['pdf', 'docx'],
                    ],
                ],
                'Analisis Performa Media Sosial' => [
                    ['type' => 'video', 'title' => 'Menggunakan Insight Platform Media Sosial', 'duration' => 300],
                    ['type' => 'text', 'title' => 'Memahami Metrik Kunci dan Membuat Laporan Performa', 'body' => 'Interpretasi data untuk pengambilan keputusan.', 'format' => 'markdown'],
                    ['type' => 'practice', 'title' => 'Latihan Analisis Data Media Sosial', 'description' => 'Praktik menganalisis data performa.', 'time_limit_minutes' => 30, 'questions' => [
                        ['question' => 'Metrik "Engagement Rate" biasanya dihitung dengan cara apa?', 'type' => 'short_answer', 'answer_key' => '(Jumlah total interaksi (suka, komentar, bagikan) / Jumlah total pengikut) x 100%.', 'explanation' => 'Ini adalah cara umum untuk mengukur seberapa aktif audiens berinteraksi dengan konten.'],
                    ]],
                ],
                'Studi Kasus & Tren Media Sosial' => [
                    ['type' => 'text', 'title' => 'Analisis Kampanye Media Sosial Sukses', 'body' => 'Belajar dari contoh-contoh terbaik.', 'format' => 'markdown'],
                    [
                        'type' => 'file',
                        'title' => 'Laporan Tren Media Sosial Terbaru',
                        'description' => 'Laporan tahunan yang merangkum tren-tren terkini di dunia media sosial, termasuk platform baru, format konten, dan perilaku pengguna.',
                        'file_name' => 'social_media_trends_report.pdf',
                        'mime_type' => 'application/pdf',
                    ],
                    [
                        'type' => 'assignment',
                        'title' => 'Presentasi Tren Media Sosial',
                        'description' => 'Riset dan presentasikan satu tren media sosial terbaru yang relevan untuk tahun ini.',
                        'instructions' => "1. Pilih satu tren (misal: video pendek, social commerce, AI-generated content).\n2. Jelaskan apa itu tren tersebut dan mengapa itu penting.\n3. Berikan contoh brand yang berhasil menggunakannya.\n4. Berikan rekomendasi bagaimana brand lain bisa mengadopsi tren ini.",
                        'submission_requirements' => ['format' => 'presentation', 'content' => 'Unggah file presentasi Anda (PPTX atau PDF).'],
                        'max_file_size_mb' => 15,
                        'allowed_file_types' => ['pptx', 'pdf'],
                    ],
                ],
            ],
            'Penulisan Konten untuk Pemula' => [
                'Dasar-dasar Penulisan Konten' => [
                    ['type' => 'video', 'title' => 'Memahami Audiens dan Tujuan Penulisan', 'duration' => 300],
                    ['type' => 'text', 'title' => 'Struktur Dasar Artikel dan Blog Post', 'body' => 'Panduan menyusun konten yang mudah dicerna.', 'format' => 'markdown'],
                    ['type' => 'quiz', 'title' => 'Kuis Dasar Penulisan', 'description' => 'Uji pemahaman Anda tentang dasar-dasar penulisan konten.', 'time_limit_minutes' => 10, 'passing_score' => 70, 'max_attempts' => 3, 'questions' => [
                        ['question' => 'Apa langkah pertama dalam menulis konten?', 'options' => ['Mulai menulis', 'Memahami audiens dan tujuan', 'Mencari gambar'], 'correct_answer' => 'Memahami audiens dan tujuan', 'explanation' => 'Memahami siapa yang Anda tulis dan mengapa Anda menulis adalah kunci.'],
                        ['question' => 'Mengapa struktur penting dalam artikel?', 'options' => ['Agar terlihat lebih panjang', 'Agar mudah dibaca dan dipahami', 'Agar cepat selesai'], 'correct_answer' => 'Agar mudah dibaca dan dipahami', 'explanation' => 'Struktur yang baik membantu pembaca mengikuti alur pemikiran Anda.'],
                        ['question' => 'Apa itu SEO-friendly content?', 'options' => ['Konten yang hanya untuk mesin pencari', 'Konten yang dioptimalkan untuk mesin pencari dan pembaca', 'Konten yang banyak gambar'], 'correct_answer' => 'Konten yang dioptimalkan untuk mesin pencari dan pembaca', 'explanation' => 'Konten SEO-friendly menyeimbangkan kebutuhan mesin pencari dan pengalaman pembaca.'],
                    ]],
                ],
                'Struktur & Gaya Penulisan Efektif' => [
                    ['type' => 'video', 'title' => 'Teknik Penulisan Persuasif dan Menarik', 'duration' => 350],
                    ['type' => 'text', 'title' => 'Penggunaan Judul, Subjudul, dan Poin-poin', 'body' => 'Meningkatkan keterbacaan konten.', 'format' => 'markdown'],
                    ['type' => 'practice', 'title' => 'Latihan Menulis Paragraf Pembuka yang Kuat', 'description' => 'Praktik menulis intro yang menarik.', 'time_limit_minutes' => 20, 'questions' => [
                        ['question' => 'Tulis ulang paragraf pembuka berikut agar lebih menarik: "Artikel ini akan membahas tentang manfaat teh hijau."', 'type' => 'short_answer', 'answer_key' => 'Contoh: "Ingin hidup lebih sehat dan berenergi? Temukan rahasia di balik secangkir teh hijau yang mungkin belum Anda ketahui."', 'explanation' => 'Paragraf pembuka yang baik harus memancing rasa ingin tahu pembaca.'],
                    ]],
                ],
                'Penulisan Konten SEO-Friendly' => [
                    ['type' => 'video', 'title' => 'Riset Kata Kunci untuk Konten SEO', 'duration' => 400],
                    ['type' => 'text', 'title' => 'Optimasi Konten dengan Kata Kunci dan Meta Deskripsi', 'body' => 'Cara menulis konten yang disukai mesin pencari.', 'format' => 'markdown'],
                    [
                        'type' => 'assignment',
                        'title' => 'Menulis Artikel Blog SEO-Friendly',
                        'description' => 'Tulis sebuah artikel blog (500-700 kata) tentang topik pilihan Anda yang dioptimalkan untuk mesin pencari.',
                        'instructions' => "1. Pilih satu kata kunci utama (main keyword) dan 2-3 kata kunci turunan (secondary keywords).\n2. Tulis artikel yang informatif dan menarik bagi pembaca.\n3. Terapkan praktik SEO on-page: gunakan kata kunci di judul, URL (jika memungkinkan), paragraf pertama, subjudul, dan secara alami di seluruh konten.\n4. Tulis juga meta description yang menarik (maksimal 160 karakter).",
                        'submission_requirements' => ['format' => 'document', 'content' => 'Unggah artikel Anda dalam format DOCX atau PDF.'],
                        'max_file_size_mb' => 2,
                        'allowed_file_types' => ['docx', 'pdf'],
                    ],
                ],
                'Penulisan untuk Berbagai Platform' => [
                    ['type' => 'video', 'title' => 'Menyesuaikan Gaya Penulisan untuk Media Sosial dan Email', 'duration' => 300],
                    ['type' => 'text', 'title' => 'Menulis Copy untuk Website dan Landing Page', 'body' => 'Strategi penulisan untuk konversi.', 'format' => 'markdown'],
                    ['type' => 'practice', 'title' => 'Latihan Menulis Caption Media Sosial', 'description' => 'Praktik menulis caption yang menarik.', 'time_limit_minutes' => 15, 'questions' => [
                        ['question' => 'Buat sebuah caption Instagram untuk mempromosikan produk sepatu baru.', 'type' => 'short_answer', 'answer_key' => 'Contoh: "Langkah baru, gaya baru! 👟 Sepatu XZ-1 kami siap menemanimu berpetualang. Dapatkan sekarang dan rasakan kenyamanannya! #sepatubaru #gayabaru #nyaman"', 'explanation' => 'Caption yang baik harus singkat, menarik, dan menyertakan ajakan bertindak (CTA) serta tagar yang relevan.'],
                    ]],
                ],
                'Revisi & Editing Konten' => [
                    ['type' => 'text', 'title' => 'Teknik Revisi dan Editing Profesional', 'body' => 'Cara menyempurnakan tulisan Anda.', 'format' => 'markdown'],
                    [
                        'type' => 'file',
                        'title' => 'Checklist Editing Konten',
                        'description' => 'Gunakan daftar periksa ini untuk memastikan tulisan Anda bebas dari kesalahan dan mudah dibaca sebelum dipublikasikan.',
                        'file_name' => 'editing_checklist.pdf',
                        'mime_type' => 'application/pdf',
                    ],
                    [
                        'type' => 'assignment',
                        'title' => 'Revisi dan Editing Artikel',
                        'description' => 'Revisi dan edit sebuah draf artikel yang disediakan untuk meningkatkan kejelasan, keterbacaan, dan tata bahasa.',
                        'instructions' => "1. Baca draf artikel yang disediakan (akan diberikan dalam bentuk file).\n2. Perbaiki semua kesalahan ejaan dan tata bahasa.\n3. Perbaiki struktur kalimat dan paragraf agar lebih mudah dibaca.\n4. Pastikan alur tulisan logis dan koheren.\n5. Berikan catatan singkat tentang perubahan besar yang Anda buat.",
                        'submission_requirements' => ['format' => 'document', 'content' => 'Unggah versi revisi dari artikel dalam format DOCX dengan fitur "Track Changes" diaktifkan, atau berikan komentar pada file PDF.'],
                        'max_file_size_mb' => 2,
                        'allowed_file_types' => ['docx', 'pdf'],
                    ],
                ],
            ],
            'Kursus Ahli SEO' => [
                'Pengantar SEO & Cara Kerja Search Engine' => [
                    ['type' => 'video', 'title' => 'Mekanisme Search Engine: Crawling, Indexing, Ranking', 'duration' => 300],
                    ['type' => 'text', 'title' => 'Faktor-faktor Peringkat Google', 'body' => 'Memahami algoritma Google.', 'format' => 'markdown'],
                    ['type' => 'quiz', 'title' => 'Kuis Pengantar SEO', 'description' => 'Uji pemahaman Anda tentang dasar-dasar SEO dan cara kerja mesin pencari.', 'time_limit_minutes' => 10, 'passing_score' => 70, 'max_attempts' => 3, 'questions' => [
                        ['question' => 'Apa itu SEO?', 'options' => ['Search Engine Optimization', 'Social Engagement Optimization', 'Sales Event Organization'], 'correct_answer' => 'Search Engine Optimization', 'explanation' => 'SEO adalah praktik meningkatkan kualitas dan kuantitas lalu lintas situs web ke situs web atau halaman web dari mesin pencari.'],
                        ['question' => 'Apa fungsi utama dari crawler (spider) mesin pencari?', 'options' => ['Mengindeks halaman web', 'Mengunjungi dan membaca halaman web', 'Menentukan peringkat halaman web'], 'correct_answer' => 'Mengunjungi dan membaca halaman web', 'explanation' => 'Crawler adalah program yang digunakan oleh mesin pencari untuk menemukan dan membaca halaman web.'],
                        ['question' => 'Apa itu SERP?', 'options' => ['Search Engine Ranking Page', 'Search Engine Result Page', 'Search Entry Point'], 'correct_answer' => 'Search Engine Result Page', 'explanation' => 'SERP adalah halaman hasil yang ditampilkan oleh mesin pencari setelah pengguna memasukkan kueri.'],
                    ]],
                ],
                'Keyword Research & Analisis Kompetitor' => [
                    ['type' => 'video', 'title' => 'Alat Riset Kata Kunci (Google Keyword Planner, Ahrefs)', 'duration' => 400],
                    ['type' => 'text', 'title' => 'Strategi Long-Tail Keyword dan LSI Keywords', 'body' => 'Menemukan peluang kata kunci tersembunyi.', 'format' => 'markdown'],
                    ['type' => 'practice', 'title' => 'Latihan Riset Kata Kunci', 'description' => 'Praktik melakukan riset kata kunci untuk niche tertentu.', 'time_limit_minutes' => 30, 'questions' => [
                        ['question' => 'Apa perbedaan antara "short-tail keyword" dan "long-tail keyword"?', 'type' => 'short_answer', 'answer_key' => 'Short-tail lebih umum dan memiliki volume pencarian tinggi (misal: "sepatu"), sedangkan long-tail lebih spesifik dan memiliki volume lebih rendah tapi intensi lebih tinggi (misal: "sepatu lari pria tahan air").', 'explanation' => 'Long-tail keyword seringkali lebih mudah untuk diranking dan menghasilkan konversi yang lebih baik.'],
                    ]],
                ],
                'On-Page SEO Optimization' => [
                    ['type' => 'video', 'title' => 'Optimasi Judul, Meta Deskripsi, dan Heading', 'duration' => 350],
                    ['type' => 'text', 'title' => 'Struktur Konten, Internal Linking, dan Optimasi Gambar', 'body' => 'Meningkatkan relevansi halaman.', 'format' => 'markdown'],
                    [
                        'type' => 'assignment',
                        'title' => 'Optimasi SEO On-Page untuk Artikel',
                        'description' => 'Lakukan optimasi on-page pada sebuah artikel yang sudah ada (akan disediakan).',
                        'instructions' => "1. Analisis artikel yang diberikan.\n2. Lakukan riset kata kunci singkat untuk topik tersebut.\n3. Tulis ulang Title Tag dan Meta Description agar lebih optimal dan menarik.\n4. Restrukturisasi heading (H1, H2, H3) jika diperlukan.\n5. Berikan rekomendasi untuk internal linking dan optimasi gambar (alt text).",
                        'submission_requirements' => ['format' => 'document', 'content' => 'Unggah dokumen yang berisi rekomendasi optimasi Anda.'],
                        'max_file_size_mb' => 2,
                        'allowed_file_types' => ['docx', 'pdf'],
                    ],
                ],
                'Off-Page SEO & Link Building' => [
                    ['type' => 'video', 'title' => 'Strategi Link Building yang Efektif dan Aman', 'duration' => 400],
                    ['type' => 'text', 'title' => 'Guest Posting, Broken Link Building, dan Skyscraper Technique', 'body' => 'Teknik mendapatkan backlink berkualitas.', 'format' => 'markdown'],
                    ['type' => 'practice', 'title' => 'Latihan Analisis Backlink Kompetitor', 'description' => 'Praktik menganalisis profil backlink kompetitor.', 'time_limit_minutes' => 25, 'questions' => [
                        ['question' => 'Mengapa backlink dari situs web dengan otoritas tinggi lebih berharga?', 'type' => 'short_answer', 'answer_key' => 'Karena mesin pencari seperti Google menganggapnya sebagai "suara kepercayaan" yang lebih kuat, yang menandakan bahwa konten Anda berkualitas dan dapat dipercaya.', 'explanation' => 'Kualitas backlink lebih penting daripada kuantitas.'],
                    ]],
                ],
                'Technical SEO & Audit Website' => [
                    ['type' => 'video', 'title' => 'Memahami Crawlability dan Indexability', 'duration' => 450],
                    ['type' => 'text', 'title' => 'Optimasi Kecepatan Situs, Mobile-Friendliness, dan Struktur Data', 'body' => 'Aspek teknis penting untuk SEO.', 'format' => 'markdown'],
                    [
                        'type' => 'assignment',
                        'title' => 'Audit SEO Teknis Website',
                        'description' => 'Lakukan audit SEO teknis dasar pada sebuah situs web pilihan Anda (bukan situs besar seperti Google atau Facebook).',
                        'instructions' => "1. Gunakan alat seperti Google PageSpeed Insights dan Screaming Frog (versi gratis) untuk menganalisis situs.\n2. Periksa aspek-aspek berikut: kecepatan situs, mobile-friendliness, status respons (kode 200, 404, 301), duplikasi judul dan meta deskripsi, dan penggunaan file robots.txt.\n3. Buat laporan audit yang merangkum temuan utama dan berikan rekomendasi perbaikan.",
                        'submission_requirements' => ['format' => 'document', 'content' => 'Unggah laporan audit Anda dalam format PDF atau DOCX.'],
                        'max_file_size_mb' => 10,
                        'allowed_file_types' => ['pdf', 'docx'],
                    ],
                ],
            ],
            'Pengembangan Aplikasi Seluler dengan Flutter' => [
                'Pengantar Flutter & Dart' => [
                    ['type' => 'video', 'title' => 'Mengapa Flutter untuk Pengembangan Mobile?', 'duration' => 300],
                    ['type' => 'text', 'title' => 'Instalasi Flutter SDK dan Konfigurasi Lingkungan', 'body' => 'Panduan lengkap untuk memulai.', 'format' => 'markdown'],
                    ['type' => 'practice', 'title' => 'Latihan Membuat Aplikasi Hello World Flutter', 'description' => 'Praktik membuat aplikasi Flutter pertama.', 'time_limit_minutes' => 20, 'questions' => [
                        ['question' => 'Tulis kode Dart untuk menampilkan teks "Hello, Flutter!" di tengah layar.', 'type' => 'code', 'answer_key' => 'import \'package:flutter/material.dart\';\n\nvoid main() {\n  runApp(\n    MaterialApp(\n      home: Scaffold(\n        body: Center(\n          child: Text(\'Hello, Flutter!\'),\n        ),\n      ),\n    ),\n  );\n}', 'explanation' => 'Kode ini menggunakan `MaterialApp`, `Scaffold`, `Center`, dan `Text` widget untuk menampilkan teks.'],
                    ]],
                ],
                'Widget & UI Dasar Flutter' => [
                    ['type' => 'video', 'title' => 'Memahami Konsep Widget dan Widget Tree', 'duration' => 350],
                    ['type' => 'text', 'title' => 'Layouting dengan Row, Column, Container, dan Stack', 'body' => 'Membangun tata letak UI yang kompleks.', 'format' => 'markdown'],
                    [
                        'type' => 'assignment',
                        'title' => 'Membangun Tampilan Login Sederhana',
                        'description' => 'Buat sebuah tampilan halaman login sederhana menggunakan widget-widget dasar Flutter.',
                        'instructions' => "1. Buat halaman yang berisi dua input field (Email dan Password) dan satu tombol (Login).\n2. Gunakan widget `Column` untuk menyusun elemen secara vertikal.\n3. Gunakan `TextField` untuk input dan `ElevatedButton` untuk tombol.\n4. Tambahkan sedikit padding di sekitar elemen agar tidak terlalu rapat.",
                        'submission_requirements' => ['format' => 'zip', 'content' => 'Unggah file `main.dart` atau seluruh proyek Flutter dalam format .zip.'],
                        'max_file_size_mb' => 10,
                        'allowed_file_types' => ['dart', 'zip'],
                    ],
                ],
                'Manajemen State & Data' => [
                    ['type' => 'video', 'title' => 'Pengenalan State Management di Flutter (Provider/BLoC)', 'duration' => 400],
                    ['type' => 'text', 'title' => 'Mengelola Data Aplikasi dengan State Management', 'body' => 'Contoh implementasi state management.', 'format' => 'markdown'],
                    ['type' => 'practice', 'title' => 'Latihan Implementasi State Management', 'description' => 'Praktik mengelola state pada aplikasi.', 'time_limit_minutes' => 35, 'questions' => [
                        ['question' => 'Apa perbedaan antara `StatelessWidget` dan `StatefulWidget`?', 'type' => 'short_answer', 'answer_key' => '`StatelessWidget` tidak memiliki state internal dan tidak dapat berubah setelah dibuat. `StatefulWidget` dapat menyimpan state yang dapat berubah selama waktu hidup widget, dan dapat digambar ulang saat state berubah.', 'explanation' => 'Memilih widget yang tepat penting untuk efisiensi aplikasi.'],
                    ]],
                ],
                'Integrasi API & Database Lokal' => [
                    ['type' => 'video', 'title' => 'Melakukan HTTP Request dengan Dio/http Package', 'duration' => 450],
                    ['type' => 'text', 'title' => 'Penyimpanan Data Lokal dengan SQLite/Hive', 'body' => 'Cara menyimpan dan mengambil data secara offline.', 'format' => 'markdown'],
                    [
                        'type' => 'assignment',
                        'title' => 'Membangun Aplikasi Daftar Belanja dengan API dan Lokal DB',
                        'description' => 'Buat aplikasi sederhana yang mengambil data dari API publik (contoh: JSONPlaceholder) dan menampilkannya dalam daftar. Tambahkan fitur untuk menyimpan data favorit secara lokal.',
                        'instructions' => "1. Gunakan package `http` atau `dio` untuk mengambil data (misalnya, daftar 'todos' atau 'posts') dari API publik.\n2. Tampilkan data dalam sebuah `ListView`.\n3. Tambahkan tombol pada setiap item untuk menyimpannya sebagai favorit.\n4. Gunakan package `shared_preferences` atau `hive` untuk menyimpan ID item favorit di penyimpanan lokal.",
                        'submission_requirements' => ['format' => 'zip', 'content' => 'Unggah seluruh proyek Flutter Anda dalam format .zip.'],
                        'max_file_size_mb' => 15,
                        'allowed_file_types' => ['zip'],
                    ],
                ],
                'Deployment Aplikasi (Android & iOS)' => [
                    ['type' => 'video', 'title' => 'Persiapan Aplikasi untuk Rilis di Play Store dan App Store', 'duration' => 500],
                    ['type' => 'text', 'title' => 'Proses Signing, Build, dan Submission Aplikasi', 'body' => 'Panduan lengkap untuk deployment.', 'format' => 'markdown'],
                    [
                        'type' => 'file',
                        'title' => 'Checklist Rilis Aplikasi Flutter',
                        'description' => 'Daftar periksa komprehensif untuk memastikan semua langkah telah diikuti sebelum merilis aplikasi Flutter ke Google Play Store dan Apple App Store.',
                        'file_name' => 'flutter_release_checklist.pdf',
                        'mime_type' => 'application/pdf',
                    ],
                ],
            ],
        ];
    }
}