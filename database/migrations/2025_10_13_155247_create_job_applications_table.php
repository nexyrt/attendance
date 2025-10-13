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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // Nama
            $table->text('alamat'); // Alamat
            $table->string('nomor_telepon', 20); // Nomor Telepon (WA)
            $table->string('email'); // Email
            $table->enum('status_penerimaan', [
                'pending',
                'interview',
                'accepted',
                'rejected',
                'on_hold'
            ])->default('pending'); // Status Penerimaan
            $table->string('posisi'); // Posisi
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete(); // Departemen
            $table->decimal('skor_kandidat', 3, 1)->nullable()->comment('AI Score 0.0-10.0'); // Skor Kandidat (AI)
            $table->enum('sumber', [
                'instagram',
                'facebook',
                'linkedin',
                'twitter',
                'jobstreet',
                'indeed',
                'referral',
                'website',
                'walk_in',
                'other'
            ])->nullable(); // Sumber
            $table->enum('daftar_melalui', [
                'manual',
                'email',
                'website',
                'whatsapp',
                'social_media',
                'referral',
                'other'
            ])->default('manual'); // Daftar Melalui
            $table->json('file_terkait')->nullable()->comment('JSON array of file paths'); // File Terkait
            $table->text('catatan')->nullable(); // Optional notes field
            $table->timestamp('tanggal_apply')->useCurrent(); // Application date
            $table->timestamps();

            // Indexes for better performance
            $table->index(['status_penerimaan', 'created_at']);
            $table->index(['department_id', 'posisi']);
            $table->index(['sumber', 'daftar_melalui']);
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};