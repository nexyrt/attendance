<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['sick', 'annual', 'important', 'other']);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');
            $table->enum('status', [
                'pending_manager',
                'pending_hr',
                'pending_director',
                'approved',
                'rejected_manager',
                'rejected_hr',
                'rejected_director',
                'cancel'
            ])->default('pending_manager');
            $table->string('attachment_path')->nullable();
            $table->string('staff_signature')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users');
            $table->timestamp('manager_approved_at')->nullable();
            $table->string('manager_signature')->nullable();
            $table->foreignId('hr_id')->nullable()->constrained('users');
            $table->timestamp('hr_approved_at')->nullable();
            $table->string('hr_signature')->nullable();
            $table->foreignId('director_id')->nullable()->constrained('users');
            $table->timestamp('director_approved_at')->nullable();
            $table->string('director_signature')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['status', 'manager_approved_at']);
            $table->index(['status', 'hr_approved_at']);
            $table->index(['status', 'director_approved_at']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
