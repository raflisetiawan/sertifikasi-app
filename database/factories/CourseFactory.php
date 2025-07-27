<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseBenefit;
use App\Models\Trainer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $courses = [
            [
                'name' => 'Pengembangan Web Full-Stack',
                'description' => 'Kursus ini akan membekali Anda dengan keterampilan yang dibutuhkan untuk menjadi pengembang web full-stack yang handal, mulai dari front-end hingga back-end.',
            ],
            [
                'name' => 'Sains Data dengan Python',
                'description' => 'Pelajari dasar-dasar Sains Data, analisis data, dan machine learning menggunakan Python. Cocok untuk pemula hingga tingkat menengah.',
            ],
            [
                'name' => 'Masterclass Pemasaran Digital',
                'description' => 'Kuasai strategi pemasaran digital terbaru, termasuk SEO, SEM, media sosial, dan email marketing untuk meningkatkan bisnis Anda.',
            ],
            [
                'name' => 'Dasar-dasar Desain UI/UX',
                'description' => 'Pahami prinsip-prinsip desain UI/UX, mulai dari riset pengguna, wireframing, prototyping, hingga pengujian kegunaan.',
            ],
            [
                'name' => 'Spesialis Media Sosial',
                'description' => 'Jadilah spesialis media sosial yang mampu membuat konten menarik, mengelola kampanye, dan menganalisis performa di berbagai platform.',
            ],
            [
                'name' => 'Penulisan Konten untuk Pemula',
                'description' => 'Panduan lengkap untuk menjadi penulis konten yang efektif dan menarik, cocok untuk blog, website, atau materi pemasaran.',
            ],
            [
                'name' => 'Kursus Ahli SEO',
                'description' => 'Selami dunia SEO dan pelajari teknik-teknik optimasi mesin pencari untuk meningkatkan peringkat website Anda di Google.',
            ],
            [
                'name' => 'Pengembangan Aplikasi Seluler dengan Flutter',
                'description' => 'Bangun aplikasi mobile lintas platform yang indah dan fungsional menggunakan Flutter dan Dart, dari konsep hingga publikasi.',
            ],
        ];

        $selectedCourse = $this->faker->randomElement($courses);

        return [
            'name' => $selectedCourse['name'],
            'description' => $selectedCourse['description'],
            'key_concepts' => $this->getKeyConceptsForCourse($selectedCourse['name']),
            'facility' => $this->faker->randomElements(['E-Sertifikat', 'Portofolio', 'Penyaluran Kerja', 'Mentoring'], 2),
            'price' => $this->faker->numberBetween(500000, 2000000),
            'place' => 'Online',
            'duration' => $this->faker->numberBetween(4, 12) . ' minggu',
            'status' => 'published',
            'operational_start' => now(),
            'operational_end' => now()->addMonths(6),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Course $course) {
            // Generic benefits that apply to all courses
            $genericBenefits = [
                [
                    'title' => 'Sertifikat Kelulusan',
                    'subtitle' => 'Bukti kompetensi yang diakui industri.',
                    'description' => 'Setelah berhasil menyelesaikan semua modul, Anda akan menerima sertifikat digital yang dapat diverifikasi.',
                    'earn_by' => 'penyelesaian',
                ],
                [
                    'title' => 'Akses Materi Seumur Hidup',
                    'subtitle' => 'Belajar kapan saja, di mana saja.',
                    'description' => 'Nikmati akses tak terbatas ke semua materi pembelajaran, termasuk video, teks, dan kuis, bahkan setelah kursus berakhir.',
                    'earn_by' => 'penyelesaian',
                ],
                [
                    'title' => 'Grup Diskusi Komunitas',
                    'subtitle' => 'Dukungan dan inspirasi dari sesama.',
                    'description' => 'Terlibat dalam grup diskusi aktif dengan sesama peserta untuk berbagi ide dan memecahkan masalah bersama.',
                    'earn_by' => 'penyelesaian',
                ],
            ];

            // Course-specific benefits
            $specificBenefits = [
                'Pengembangan Web Full-Stack' => [
                    'title' => 'Portofolio Proyek Nyata',
                    'subtitle' => 'Bangun aplikasi web dari nol.',
                    'description' => 'Terapkan keterampilan Anda dengan membangun proyek web dinamis yang siap ditambahkan ke portofolio Anda.',
                    'earn_by' => 'tugas',
                ],
                'Sains Data dengan Python' => [
                    'title' => 'Analisis Kasus Dunia Nyata',
                    'subtitle' => 'Pecahkan masalah bisnis dengan data.',
                    'description' => 'Gunakan dataset nyata untuk melakukan analisis dan membangun model prediktif menggunakan Python, Pandas, dan Scikit-learn.',
                    'earn_by' => 'tugas',
                ],
                'Masterclass Pemasaran Digital' => [
                    'title' => 'Studi Kasus Kampanye Sukses',
                    'subtitle' => 'Belajar dari strategi terbaik.',
                    'description' => 'Analisis mendalam tentang kampanye pemasaran digital yang berhasil untuk menginspirasi strategi Anda sendiri.',
                    'earn_by' => 'penyelesaian',
                ],
                'Dasar-dasar Desain UI/UX' => [
                    'title' => 'Desain Aplikasi Interaktif',
                    'subtitle' => 'Dari wireframe hingga prototipe.',
                    'description' => 'Rancang dan bangun prototipe aplikasi yang fungsional dan menarik menggunakan alat desain standar industri.',
                    'earn_by' => 'tugas',
                ],
                'Pengembangan Aplikasi Seluler dengan Flutter' => [
                    'title' => 'Bangun Aplikasi Lintas Platform',
                    'subtitle' => 'Satu kode untuk Android dan iOS.',
                    'description' => 'Kembangkan dan deploy aplikasi seluler yang berfungsi di kedua platform utama dari satu basis kode.',
                    'earn_by' => 'tugas',
                ],
            ];
        });
    }

    /**
     * Get key concepts relevant to the course name.
     *
     * @param string $courseName
     * @return array
     */
    private function getKeyConceptsForCourse(string $courseName): array
    {
        $conceptMap = [
            'Pengembangan Web Full-Stack' => ['HTML', 'CSS', 'JavaScript', 'PHP', 'Laravel', 'Vue.js', 'Database', 'API'],
            'Sains Data dengan Python' => ['Python', 'Pandas', 'NumPy', 'Machine Learning', 'Statistika', 'Visualisasi Data'],
            'Masterclass Pemasaran Digital' => ['SEO', 'SEM', 'Media Sosial', 'Email Marketing', 'Content Marketing', 'Analisis Data Pemasaran'],
            'Dasar-dasar Desain UI/UX' => ['User Research', 'Wireframing', 'Prototyping', 'Usability Testing', 'Figma', 'Adobe XD'],
            'Spesialis Media Sosial' => ['Strategi Konten', 'Manajemen Komunitas', 'Iklan Media Sosial', 'Analisis Performa', 'Platform Media Sosial'],
            'Penulisan Konten untuk Pemula' => ['Struktur Artikel', 'SEO Writing', 'Copywriting', 'Riset Kata Kunci', 'Gaya Penulisan'],
            'Kursus Ahli SEO' => ['On-page SEO', 'Off-page SEO', 'Technical SEO', 'Keyword Research', 'Google Analytics', 'Search Console'],
            'Pengembangan Aplikasi Seluler dengan Flutter' => ['Flutter', 'Dart', 'UI/UX Mobile', 'State Management', 'Integrasi API', 'Deployment'],
        ];

        return $conceptMap[$courseName] ?? $this->faker->randomElements(['Konsep Dasar', 'Praktik Terbaik', 'Studi Kasus'], 3);
    }
}
