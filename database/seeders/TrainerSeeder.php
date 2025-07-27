<?php

namespace Database\Seeders;

use App\Models\Trainer;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class TrainerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the destination directory exists
        Storage::disk('public')->makeDirectory('trainers/images');

        $sourcePath = public_path('img/trainer-example');
        $imageFiles = \Illuminate\Support\Facades\File::files($sourcePath);

        if (empty($imageFiles)) {
            $this->command->info('No images found in public/img/trainer-example.');
            return;
        }

        $trainersData = [
            [
                'name' => 'Marisya Mahdia Khoirina, S.M, M.M, CDM',
                'email' => 'marisya.khoirina@example.com',
                'qualification' => 'Dosen Manajemen UISI, Digital Marketer',
                'description' => 'Selain mengajar sejak tahun 2015, marisya juga menjabat sebagai kepala bagian admisi dan promosi UISI sejak tahun 2021. Pengalaman ini praktis menjadikan marisya instruktur handal di bidangnya.',
                'image_file' => 'Picture1.png',
            ],
            [
                'name' => 'Tyas Ajeng Nastiti, S.T, M.Ds,',
                'email' => 'tyas.nastiti@example.com',
                'qualification' => 'Dosen DKV UISI, Entrepreneur, Digital Creator',
                'description' => 'Selain mengajar, Tyas adalah digital creator degan verified account pada berbagai macam platform media sosial. Hal ini menjadikan tyas tepat menjadi instruktur pada pelatihan social media specialist. Background edukasi pada bidang desain, menjadikannya mampu untuk memberikan pelatihan juga di pelatihan desain grafis.',
                'image_file' => 'Picture2.png',
            ],
            [
                'name' => 'Muhammad Nasrulloh, S.T., M.T',
                'email' => 'muhammad.nasrulloh@example.com',
                'qualification' => 'Dosen DKV UISI, Animator, Game Designer, Illustrator',
                'description' => 'Sejak tahun 2016, Nasrul telah menjadi seorang pengajar di bidang Desain Komunikasi Visual. Fokus pengajarannya melibatkan Multimedia dan Animasi, serta Ilustrasi dan menggambar dasar. Tidak hanya mengajar, melainkan juga seorang kreator yang berusaha menciptakan atmosfer kelas yang penuh dengan petualangan kreatif terutama dalam kelas Animasi. Di luar jam perkuliahan, sering terlibat dalam proyek ilustrasi dan animasi bahkan terlibat dalam memberikan beberapa pelatihan.',
                'image_file' => 'Picture3.jpg',
            ],
            [
                'name' => 'Brina Miftahurrohmah, S.Si., M.Si., MCE',
                'email' => 'brina.miftahurrohmah@example.com',
                'qualification' => 'Dosen Sistem Informasi UISI, Data Scientist',
                'description' => 'Sejak tahun 2018, Brina telah menjadi dosen di program studi Sistem Informasi UISI. Saat ini, ia fokus pada pengembangan keahlian di bidang Data Science yang meliputi: Intelligence Decision Support Systems, Statistika, dan Peramalan. Brina telah meraih sertifikasi sebagai Microsoft Certified Educator (MCE) dan Microsoft Certified Fundamentals (MCF). Pengalaman mengajarnya tidak hanya terbatas di lingkungan kampus, melainkan juga sebagai instruktur aktif dalam pelatihan Microsoft Azure Data Fundamentals di bawah program Fresh Graduate Akademi Digital Talent Scholarship (FGA-DTS) oleh Kementerian Komunikasi dan Informatika. Brina juga telah memberikan kontribusi sebagai pengajar pada berbagai acara pelatihan seputar Microsoft Excel. Keberagaman pengalaman pengajaran dan sertifikasi yang dimilikinya menciptakan lingkungan pembelajaran yang menarik dan relevan dengan perkembangan terbaru dalam dunia teknologi informasi.',
                'image_file' => 'Picture4.png',
            ],
        ];

        foreach ($trainersData as $trainerData) {
            $imagePath = $sourcePath . '/' . $trainerData['image_file'];
            $fullPath = null;

            if (\Illuminate\Support\Facades\File::exists($imagePath)) {
                $fullPath = Storage::disk('public')->putFile('trainers/images', new File($imagePath));
            } else {
                $this->command->warn("Image file not found: " . $imagePath);
            }

            Trainer::factory()->create([
                'name' => $trainerData['name'],
                'email' => $trainerData['email'],
                'qualification' => $trainerData['qualification'],
                'description' => $trainerData['description'],
                'image' => $fullPath,
                'starred' => true, // Set all to starred for now, can be adjusted
            ]);
        }
    }
}
