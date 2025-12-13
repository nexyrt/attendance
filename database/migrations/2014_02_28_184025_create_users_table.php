<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            // role column removed - using Spatie Permission instead
            $table->string('phone_number', 20)->nullable();
            $table->date('birthdate')->nullable();
            $table->decimal('salary', 12, 2)->nullable();
            $table->text('address')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
