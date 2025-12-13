<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('alamat');
            $table->string('nomor_telepon', 20);
            $table->string('email');
            $table->enum('status_penerimaan', ['pending', 'interview', 'accepted', 'rejected', 'on_hold'])->default('pending');
            $table->string('posisi');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->decimal('skor_kandidat', 3, 1)->nullable()->comment('AI Score 0.0-10.0');
            $table->enum('sumber', ['instagram', 'facebook', 'linkedin', 'twitter', 'jobstreet', 'indeed', 'referral', 'website', 'walk_in', 'other'])->nullable();
            $table->enum('daftar_melalui', ['manual', 'email', 'website', 'whatsapp', 'social_media', 'referral', 'other'])->default('manual');
            $table->json('file_terkait')->nullable()->comment('JSON array of file paths');
            $table->text('catatan')->nullable();
            $table->timestamp('tanggal_apply')->useCurrent();
            $table->timestamps();
            $table->index(['status_penerimaan', 'created_at']);
            $table->index(['department_id', 'posisi']);
            $table->index(['sumber', 'daftar_melalui']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
