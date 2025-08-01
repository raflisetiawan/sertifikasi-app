    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::create('courses', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Kolom nama
                $table->text('description'); // Kolom deskripsi
                $table->json('key_concepts')->nullable(); // Kolom deskripsi
                $table->json('facility'); // Kolom fasilitas
                $table->decimal('price', 10, 2); // Kolom harga (contoh: 10.00)
                $table->string('place'); // Kolom tempat
                $table->string('duration'); // Kolom waktu (contoh: 1 jam)
                $table->string('image')->nullable(); // Kolom gambar (opsional)
                $table->timestamp('operational_start')->nullable(); // Tanggal dan waktu awal operasional
                $table->timestamp('operational_end')->nullable();
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
                $table->string('syllabus_path')->nullable();

                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('courses');
        }
    };
