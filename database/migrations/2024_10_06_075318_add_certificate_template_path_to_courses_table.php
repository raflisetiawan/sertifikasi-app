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
        Schema::table('courses', function (Blueprint $table) {
            // Menambahkan kolom certificate_template_path untuk menyimpan path template sertifikat
            $table->string('certificate_template_path')->nullable()->after('guidelines');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Menghapus kolom certificate_template_path jika rollback dilakukan
            $table->dropColumn('certificate_template_path');
        });
    }
};
