<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Module;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    protected $faker;

    public function run()
    {
        $this->faker = \Faker\Factory::create();
        $courses = Course::all();

        if ($courses->isEmpty()) {
            $this->command->info('No courses found, please seed courses first.');
            return;
        }

        foreach ($courses as $course) {
            $this->command->info("Seeding modules for course: {$course->name} (ID: {$course->id})");

            $courseModuleData = [
                'Pengembangan Web Full-Stack' => [
                    'category' => 'full-stack',
                    'modules' => [
                        ['title' => 'Pengantar Pengembangan Web', 'subtitle' => 'Memahami ekosistem web dan arsitektur aplikasi.'],
                        ['title' => 'Dasar-dasar Front-End (HTML, CSS, JS)', 'subtitle' => 'Membangun antarmuka pengguna interaktif.'],
                        ['title' => 'Pengenalan Back-End (PHP & Laravel)', 'subtitle' => 'Membangun API dan mengelola database.'],
                        ['title' => 'Integrasi Front-End & Back-End', 'subtitle' => 'Menghubungkan UI dengan logika server.'],
                        ['title' => 'Deployment & Skalabilitas', 'subtitle' => 'Mempersiapkan aplikasi untuk produksi.'],
                    ],
                ],
                'Sains Data dengan Python' => [
                    'category' => 'data-science',
                    'modules' => [
                        ['title' => 'Pengantar Sains Data & Python', 'subtitle' => 'Memulai perjalanan Anda di dunia data.'],
                        ['title' => 'Analisis Data dengan Pandas', 'subtitle' => 'Memanipulasi dan membersihkan data.'],
                        ['title' => 'Visualisasi Data dengan Matplotlib & Seaborn', 'subtitle' => 'Menceritakan kisah dari data.'],
                        ['title' => 'Machine Learning Dasar dengan Scikit-learn', 'subtitle' => 'Membangun model prediktif sederhana.'],
                        ['title' => 'Proyek Akhir Sains Data', 'subtitle' => 'Menerapkan semua yang telah dipelajari.'],
                    ],
                ],
                'Masterclass Pemasaran Digital' => [
                    'category' => 'digital-marketing',
                    'modules' => [
                        ['title' => 'Strategi Pemasaran Digital', 'subtitle' => 'Merancang kampanye yang efektif.'],
                        ['title' => 'SEO & SEM', 'subtitle' => 'Meningkatkan visibilitas di mesin pencari.'],
                        ['title' => 'Pemasaran Media Sosial', 'subtitle' => 'Membangun kehadiran merek yang kuat.'],
                        ['title' => 'Email Marketing & Otomatisasi', 'subtitle' => 'Membangun hubungan dengan pelanggan.'],
                        ['title' => 'Analisis & Pelaporan Pemasaran', 'subtitle' => 'Mengukur keberhasilan kampanye.'],
                    ],
                ],
                'Dasar-dasar Desain UI/UX' => [
                    'category' => 'ui-ux',
                    'modules' => [
                        ['title' => 'Pengantar UI/UX & Riset Pengguna', 'subtitle' => 'Memahami kebutuhan pengguna.'],
                        ['title' => 'Wireframing & Prototyping', 'subtitle' => 'Membangun kerangka desain.'],
                        ['title' => 'Prinsip Desain Visual & Interaksi', 'subtitle' => 'Menciptakan pengalaman yang menarik.'],
                        ['title' => 'Usability Testing & Iterasi', 'subtitle' => 'Menguji dan menyempurnakan desain.'],
                        ['title' => 'Portofolio Desain UI/UX', 'subtitle' => 'Mempresentasikan karya Anda.'],
                    ],
                ],
                'Spesialis Media Sosial' => [
                    'category' => 'sosmed',
                    'modules' => [
                        ['title' => 'Strategi Konten Media Sosial', 'subtitle' => 'Merencanakan konten yang menarik.'],
                        ['title' => 'Manajemen Komunitas & Engagement', 'subtitle' => 'Membangun interaksi dengan audiens.'],
                        ['title' => 'Iklan Berbayar di Media Sosial', 'subtitle' => 'Mengoptimalkan kampanye iklan.'],
                        ['title' => 'Analisis Performa Media Sosial', 'subtitle' => 'Mengukur dampak dan ROI.'],
                        ['title' => 'Studi Kasus & Tren Media Sosial', 'subtitle' => 'Belajar dari praktik terbaik.'],
                    ],
                ],
                'Penulisan Konten untuk Pemula' => [
                    'category' => 'writing',
                    'modules' => [
                        ['title' => 'Dasar-dasar Penulisan Konten', 'subtitle' => 'Memahami audiens dan tujuan penulisan.'],
                        ['title' => 'Struktur & Gaya Penulisan Efektif', 'subtitle' => 'Menulis dengan jelas dan persuasif.'],
                        ['title' => 'Penulisan Konten SEO-Friendly', 'subtitle' => 'Mengoptimalkan konten untuk mesin pencari.'],
                        ['title' => 'Penulisan untuk Berbagai Platform', 'subtitle' => 'Menyesuaikan gaya untuk blog, media sosial, dll.'],
                        ['title' => 'Revisi & Editing Konten', 'subtitle' => 'Menyempurnakan tulisan Anda.'],
                    ],
                ],
                'Kursus Ahli SEO' => [
                    'category' => 'seo',
                    'modules' => [
                        ['title' => 'Pengantar SEO & Cara Kerja Search Engine', 'subtitle' => 'Memahami dasar-dasar optimasi mesin pencari.'],
                        ['title' => 'Keyword Research & Analisis Kompetitor', 'subtitle' => 'Menemukan kata kunci yang tepat.'],
                        ['title' => 'On-Page SEO Optimization', 'subtitle' => 'Mengoptimalkan elemen di halaman web.'],
                        ['title' => 'Off-Page SEO & Link Building', 'subtitle' => 'Membangun otoritas domain.'],
                        ['title' => 'Technical SEO & Audit Website', 'subtitle' => 'Memastikan website ramah mesin pencari.'],
                    ],
                ],
                'Pengembangan Aplikasi Seluler dengan Flutter' => [
                    'category' => 'mobile',
                    'modules' => [
                        ['title' => 'Pengantar Flutter & Dart', 'subtitle' => 'Memulai pengembangan aplikasi lintas platform.'],
                        ['title' => 'Widget & UI Dasar Flutter', 'subtitle' => 'Membangun antarmuka pengguna yang indah.'],
                        ['title' => 'Manajemen State & Data', 'subtitle' => 'Mengelola data dalam aplikasi.'],
                        ['title' => 'Integrasi API & Database Lokal', 'subtitle' => 'Menghubungkan aplikasi dengan layanan eksternal.'],
                        ['title' => 'Deployment Aplikasi (Android & iOS)', 'subtitle' => 'Mempublikasikan aplikasi Anda.'],
                    ],
                ],
            ];

            $specificModules = $courseModuleData[$course->name] ?? null;
            $numModules = $specificModules ? count($specificModules['modules']) : $this->faker->numberBetween(3, 5);
            $currentDate = Carbon::now();

            for ($i = 0; $i < $numModules; $i++) {
                $type = 'module';
                if ($i === 0) {
                    $type = 'prework';
                } elseif ($i === $numModules - 1) {
                    $type = 'final';
                }

                $isRestricted = $this->faker->boolean(70); // 70% chance of being restricted
                $accessStart = null;
                $accessEnd = null;

                if ($isRestricted) {
                    $accessStart = $currentDate->copy()->addDays($i * 3);
                    $accessEnd = $accessStart->copy()->addDays($this->faker->numberBetween(5, 10));
                }

                $moduleTitle = $specificModules ? $specificModules['modules'][$i]['title'] : $this->faker->sentence(3, true);
                $moduleSubtitle = $specificModules ? $specificModules['modules'][$i]['subtitle'] : $this->faker->sentence(5, true);
                $thumbnailCategory = $specificModules ? $specificModules['category'] : $this->faker->randomElement(['data-science', 'digital-marketing', 'full-stack', 'mobile', 'seo', 'sosmed', 'ui-ux', 'writing']);
                $thumbnail = "{$thumbnailCategory}-{$this->faker->numberBetween(1, 4)}.jpg";

                $moduleDescriptions = [
                    'Pengembangan Web Full-Stack' => [
                        'Pengantar Pengembangan Web' => 'Modul ini memperkenalkan Anda pada dasar-dasar pengembangan web, termasuk bagaimana internet bekerja, peran browser dan server, serta konsep dasar HTML, CSS, dan JavaScript. Anda akan memahami arsitektur aplikasi web dan ekosistem teknologi yang relevan.',
                        'Dasar-dasar Front-End (HTML, CSS, JS)' => 'Pelajari cara membangun antarmuka pengguna yang menarik dan responsif menggunakan HTML untuk struktur, CSS untuk styling, dan JavaScript untuk interaktivitas. Modul ini mencakup konsep dasar DOM manipulation, event handling, dan penggunaan framework CSS seperti Bootstrap.',
                        'Pengenalan Back-End (PHP & Laravel)' => 'Modul ini fokus pada pengembangan sisi server menggunakan PHP dan framework Laravel. Anda akan belajar tentang routing, controller, model, migrasi database, dan cara membangun RESTful API untuk aplikasi web Anda.',
                        'Integrasi Front-End & Back-End' => 'Pelajari cara menghubungkan antarmuka pengguna (front-end) dengan logika sisi server (back-end). Modul ini mencakup penggunaan AJAX, fetching data dari API, dan mengelola state aplikasi untuk menciptakan pengalaman pengguna yang mulus.',
                        'Deployment & Skalabilitas' => 'Modul terakhir ini membahas cara mempersiapkan aplikasi web Anda untuk produksi. Anda akan belajar tentang proses deployment, konfigurasi server, optimasi performa, dan strategi skalabilitas untuk menangani lalu lintas pengguna yang tinggi.',
                    ],
                    'Sains Data dengan Python' => [
                        'Pengantar Sains Data & Python' => 'Modul ini adalah titik awal Anda dalam sains data, memperkenalkan konsep dasar, peran seorang ilmuwan data, dan mengapa Python menjadi bahasa pilihan. Anda akan menginstal lingkungan pengembangan dan menulis kode Python pertama Anda.',
                        'Analisis Data dengan Pandas' => 'Pelajari cara memanipulasi, membersihkan, dan menganalisis dataset menggunakan library Pandas di Python. Modul ini mencakup teknik-teknik seperti filtering, grouping, merging, dan handling missing values untuk mempersiapkan data Anda.',
                        'Visualisasi Data dengan Matplotlib & Seaborn' => 'Kuasai seni menceritakan kisah melalui data dengan visualisasi yang efektif. Anda akan belajar membuat berbagai jenis plot seperti scatter plots, bar charts, histograms, dan heatmaps menggunakan Matplotlib dan Seaborn.',
                        'Machine Learning Dasar dengan Scikit-learn' => 'Modul ini memperkenalkan Anda pada algoritma machine learning dasar seperti regresi linear, klasifikasi, dan clustering. Anda akan menggunakan library Scikit-learn untuk membangun, melatih, dan mengevaluasi model prediktif.',
                        'Proyek Akhir Sains Data' => 'Terapkan semua keterampilan yang telah Anda pelajari dalam proyek sains data end-to-end. Anda akan memilih dataset, melakukan analisis, membangun model, dan mempresentasikan temuan Anda, mensimulasikan skenario dunia nyata.',
                    ],
                    'Masterclass Pemasaran Digital' => [
                        'Strategi Pemasaran Digital' => 'Modul ini membahas dasar-dasar perumusan strategi pemasaran digital yang komprehensif. Anda akan belajar mengidentifikasi target audiens, menetapkan tujuan, dan memilih saluran yang tepat untuk mencapai hasil maksimal.',
                        'SEO & SEM' => 'Pelajari cara meningkatkan visibilitas bisnis Anda di mesin pencari. Modul ini mencakup optimasi mesin pencari (SEO) untuk peringkat organik dan pemasaran mesin pencari (SEM) melalui iklan berbayar seperti Google Ads.',
                        'Pemasaran Media Sosial' => 'Kuasai strategi untuk membangun kehadiran merek yang kuat di berbagai platform media sosial. Anda akan belajar tentang pembuatan konten yang menarik, jadwal posting, dan cara berinteraksi dengan audiens Anda.',
                        'Email Marketing & Otomatisasi' => 'Modul ini mengajarkan cara membangun daftar email, merancang kampanye email yang efektif, dan menggunakan otomatisasi untuk memelihara hubungan dengan pelanggan. Anda akan memahami metrik penting dalam email marketing.',
                        'Analisis & Pelaporan Pemasaran' => 'Pelajari cara mengukur keberhasilan kampanye pemasaran digital Anda. Modul ini mencakup penggunaan alat analisis seperti Google Analytics untuk melacak kinerja, memahami data, dan membuat laporan yang informatif.',
                    ],
                    'Dasar-dasar Desain UI/UX' => [
                        'Pengantar UI/UX & Riset Pengguna' => 'Modul ini memperkenalkan Anda pada dunia UI/UX, menjelaskan perbedaan antara keduanya, dan mengapa riset pengguna sangat penting. Anda akan belajar teknik-teknik riset seperti wawancara dan survei untuk memahami kebutuhan pengguna.',
                        'Wireframing & Prototyping' => 'Pelajari cara mengubah ide menjadi kerangka desain yang konkret. Modul ini mencakup pembuatan wireframe (sketsa kasar) dan prototipe interaktif menggunakan alat desain untuk memvisualisasikan alur pengguna dan fungsionalitas.',
                        'Prinsip Desain Visual & Interaksi' => 'Kuasai prinsip-prinsip desain visual seperti tipografi, warna, layout, dan hierarki visual. Anda juga akan belajar tentang prinsip desain interaksi untuk menciptakan pengalaman pengguna yang intuitif dan menyenangkan.',
                        'Usability Testing & Iterasi' => 'Modul ini mengajarkan cara menguji desain Anda dengan pengguna nyata untuk mengidentifikasi masalah dan area perbaikan. Anda akan belajar tentang metode usability testing dan bagaimana menggunakan feedback untuk mengiterasi dan menyempurnakan desain.',
                        'Portofolio Desain UI/UX' => 'Bangun portofolio desain UI/UX yang kuat untuk memamerkan keterampilan Anda kepada calon pemberi kerja. Modul ini akan memandu Anda dalam memilih proyek, mendokumentasikan proses desain, dan mempresentasikan karya Anda secara efektif.',
                    ],
                    'Spesialis Media Sosial' => [
                        'Strategi Konten Media Sosial' => 'Modul ini membahas cara merencanakan dan membuat strategi konten yang menarik dan relevan untuk berbagai platform media sosial. Anda akan belajar tentang jenis konten, kalender editorial, dan cara menyesuaikan pesan untuk audiens yang berbeda.',
                        'Manajemen Komunitas & Engagement' => 'Pelajari cara membangun dan mengelola komunitas online yang aktif. Modul ini mencakup teknik-teknik untuk meningkatkan engagement, merespons komentar dan pesan, serta menangani krisis di media sosial.',
                        'Iklan Berbayar di Media Sosial' => 'Kuasai seni mengoptimalkan kampanye iklan berbayar di platform seperti Facebook, Instagram, dan TikTok. Anda akan belajar tentang penargetan audiens, anggaran, format iklan, dan analisis kinerja iklan.',
                        'Analisis Performa Media Sosial' => 'Modul ini mengajarkan cara mengukur dampak dan ROI dari upaya media sosial Anda. Anda akan belajar menggunakan alat analisis bawaan platform dan pihak ketiga untuk melacak metrik penting dan membuat laporan.',
                        'Studi Kasus & Tren Media Sosial' => 'Pelajari dari studi kasus kampanye media sosial yang sukses dan identifikasi tren terbaru dalam industri. Modul ini akan membantu Anda tetap relevan dan inovatif dalam strategi media sosial Anda.',
                    ],
                    'Penulisan Konten untuk Pemula' => [
                        'Dasar-dasar Penulisan Konten' => 'Modul ini memperkenalkan Anda pada prinsip dasar penulisan konten, termasuk memahami audiens target, menentukan tujuan penulisan, dan menyusun pesan yang jelas dan efektif.',
                        'Struktur & Gaya Penulisan Efektif' => 'Pelajari cara menyusun artikel, posting blog, atau materi pemasaran dengan struktur yang logis dan gaya penulisan yang menarik. Modul ini mencakup penggunaan judul, subjudul, paragraf, dan poin-poin untuk meningkatkan keterbacaan.',
                        'Penulisan Konten SEO-Friendly' => 'Kuasai teknik penulisan konten yang dioptimalkan untuk mesin pencari (SEO). Anda akan belajar tentang riset kata kunci, penempatan kata kunci, dan cara menulis konten yang disukai oleh Google dan pembaca.',
                        'Penulisan untuk Berbagai Platform' => 'Modul ini mengajarkan cara menyesuaikan gaya dan format penulisan Anda untuk berbagai platform, seperti blog, media sosial, email marketing, dan website. Anda akan memahami perbedaan audiens dan tujuan di setiap platform.',
                        'Revisi & Editing Konten' => 'Pelajari pentingnya revisi dan editing dalam proses penulisan. Modul ini mencakup teknik-teknik untuk mengidentifikasi kesalahan tata bahasa, ejaan, dan gaya, serta cara menyempurnakan tulisan Anda agar lebih persuasif dan bebas kesalahan.',
                    ],
                    'Kursus Ahli SEO' => [
                        'Pengantar SEO & Cara Kerja Search Engine' => 'Modul ini memberikan pemahaman mendalam tentang apa itu SEO, mengapa penting, dan bagaimana mesin pencari seperti Google bekerja. Anda akan belajar tentang crawling, indexing, dan ranking.',
                        'Keyword Research & Analisis Kompetitor' => 'Kuasai seni menemukan kata kunci yang tepat untuk target audiens Anda. Modul ini mencakup teknik riset kata kunci, analisis volume pencarian, dan cara menganalisis strategi kata kunci kompetitor.',
                        'On-Page SEO Optimization' => 'Pelajari cara mengoptimalkan elemen-elemen di halaman web Anda untuk meningkatkan peringkat. Ini termasuk optimasi judul, meta deskripsi, heading, konten, gambar, dan struktur URL.',
                        'Off-Page SEO & Link Building' => 'Modul ini fokus pada faktor-faktor di luar halaman yang memengaruhi peringkat SEO, terutama backlink. Anda akan belajar tentang strategi link building yang etis dan efektif untuk membangun otoritas domain.',
                        'Technical SEO & Audit Website' => 'Pahami aspek teknis SEO yang memastikan website Anda ramah mesin pencari. Ini mencakup kecepatan situs, mobile-friendliness, struktur situs, sitemap, robots.txt, dan cara melakukan audit SEO teknis.',
                    ],
                    'Pengembangan Aplikasi Seluler dengan Flutter' => [
                        'Pengantar Flutter & Dart' => 'Modul ini adalah langkah pertama Anda dalam membangun aplikasi seluler lintas platform dengan Flutter dan bahasa pemrograman Dart. Anda akan menginstal Flutter SDK dan membuat aplikasi Flutter pertama Anda.',
                        'Widget & UI Dasar Flutter' => 'Pelajari dasar-dasar membangun antarmuka pengguna yang indah dan responsif di Flutter menggunakan berbagai widget. Anda akan memahami konsep seperti StatelessWidget, StatefulWidget, layout, dan navigasi.',
                        'Manajemen State & Data' => 'Kuasai berbagai teknik manajemen state di Flutter untuk mengelola data dan interaksi pengguna dalam aplikasi Anda. Modul ini mencakup Provider, BLoC, GetX, atau Riverpod.',
                        'Integrasi API & Database Lokal' => 'Pelajari cara menghubungkan aplikasi Flutter Anda dengan layanan backend melalui API RESTful. Anda juga akan belajar tentang penyimpanan data lokal menggunakan SQLite atau Hive untuk fungsionalitas offline.',
                        'Deployment Aplikasi (Android & iOS)' => 'Modul terakhir ini membahas proses persiapan dan publikasi aplikasi Flutter Anda ke Google Play Store dan Apple App Store. Anda akan belajar tentang signing, build configuration, dan proses submission.',
                    ],
                ];

                $moduleDescription = $moduleDescriptions[$course->name][$moduleTitle] ?? $this->faker->paragraph(3, true);

                Module::factory()->create([
                    'course_id' => $course->id,
                    'title' => $moduleTitle,
                    'subtitle' => $moduleSubtitle,
                    'description' => $moduleDescription,
                    'order' => $i + 1,
                    'type' => $type,
                    'estimated_time_min' => $this->faker->numberBetween(30, 240),
                    'is_access_restricted' => $isRestricted,
                    'access_start_at' => $accessStart,
                    'access_end_at' => $accessEnd,
                    'thumbnail' => $thumbnail,
                ]);
            }
        }
    }
}
