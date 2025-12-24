<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mood_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('mood_type');
            $table->tinyInteger('intensity')->unsigned();
            $table->text('notes')->nullable();
            $table->json('factors')->nullable();
            $table->json('activities')->nullable();
            $table->decimal('sleep_hours', 3, 1)->nullable();
            $table->boolean('is_private')->default(true);
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['user_id', 'recorded_at']);
            $table->index(['mood_type', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mood_entries');
    }
};
