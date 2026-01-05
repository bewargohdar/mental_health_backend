<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_availabilities', function (Blueprint $table) {
            $table->date('specific_date')->nullable()->after('day_of_week');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_availabilities', function (Blueprint $table) {
            $table->dropColumn('specific_date');
        });
    }
};
