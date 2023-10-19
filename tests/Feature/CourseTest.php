<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase; // Memastikan pengujian berjalan dalam database yang terisolasi

    public function testStore()
    {
        Storage::fake('public'); // Menyiapkan penyimpanan palsu untuk pengujian

        $data = [
            'name' => 'Nama Kursus',
            'description' => 'Deskripsi Kursus',
            'facility' => 'Fasilitas Kursus',
            'price' => 100.00,
            'place' => 'Tempat Kursus',
            'time' => '1 jam',
            'operational' => '2023-02-01',
        ];

        $response = $this->postJson('/api/courses', [
            'name' => 'Nama Kursus',
            'description' => 'Deskripsi Kursus',
            'facility' => 'Fasilitas Kursus',
            'price' => 100.00,
            'place' => 'Tempat Kursus',
            'time' => '1 jam',
            'operational' => '2023-02-01',
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'created' => true,
            ]);

        // Pastikan data disimpan dalam database
        $this->assertDatabaseHas('courses', $data);

        // Jika Anda ingin melakukan pengujian untuk pengunggahan gambar, Anda dapat menambahkan kode berikut
        $file = UploadedFile::fake()->image('test.jpg');
        $data['image'] = $file;

        $response = $this->json('POST', '/api/courses', $data);

        $response->assertStatus(201); // Memastikan bahwa status respons adalah 201 (Created)

        // Pastikan data dengan gambar disimpan dalam database
        $this->assertDatabaseHas('courses', [
            'name' => 'Nama Kursus',
            'description' => 'Deskripsi Kursus',
            'facility' => 'Fasilitas Kursus',
            'price' => 100.00,
            'place' => 'Tempat Kursus',
            'time' => '1 jam',
            'operational' => '2023-02-01',
            'image' => 'courses/' . $file->hashName(), // Sesuaikan dengan direktori penyimpanan yang Anda gunakan
        ]);
    }
}
