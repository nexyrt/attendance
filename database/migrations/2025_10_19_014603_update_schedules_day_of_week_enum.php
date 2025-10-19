<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE schedules MODIFY COLUMN day_of_week ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE schedules MODIFY COLUMN day_of_week ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday') NOT NULL");
    }
};
