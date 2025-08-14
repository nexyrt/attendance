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
        Schema::create('schedule_exceptions', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->date('date');
            $table->enum('status', ['regular', 'event', 'holiday']);
            $table->time('start_time')->nullable(); 
            $table->time('end_time')->nullable();   
            $table->integer('late_tolerance')->nullable()->default(30);
            $table->text('note')->nullable();       
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_exceptions');
    }
};
