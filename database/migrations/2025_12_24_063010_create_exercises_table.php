<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('instructions');
            $table->string('category')->nullable();
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->unsignedInteger('duration')->default(0);
            $table->string('difficulty')->default('beginner');
            $table->string('audio_url')->nullable();
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('completions_count')->default(0);
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'category']);
            $table->index(['difficulty', 'is_published']);
        });

        Schema::create('exercise_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('exercise_id')->constrained()->onDelete('cascade');
            $table->timestamp('completed_at');
            $table->text('notes')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'exercise_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_completions');
        Schema::dropIfExists('exercises');
    }
};
