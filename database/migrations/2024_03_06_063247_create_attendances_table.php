<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->datetime('check_in')->nullable();
            $table->datetime('check_out')->nullable();
            $table->decimal('late_hours', 5, 2)->nullable();
            $table->enum('status', ['present', 'late', 'early_leave', 'holiday', 'pending present'])->default('present');
            $table->decimal('working_hours', 5, 2)->nullable();
            $table->text('early_leave_reason')->nullable();
            $table->text('notes')->nullable();

            // Location tracking
            $table->decimal('check_in_latitude', 10, 8)->nullable();
            $table->decimal('check_in_longitude', 11, 8)->nullable();
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();
            $table->foreignId('check_in_office_id')->nullable()->constrained('office_locations')->nullOnDelete();
            $table->foreignId('check_out_office_id')->nullable()->constrained('office_locations')->nullOnDelete();

            // Device info
            $table->string('device_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
