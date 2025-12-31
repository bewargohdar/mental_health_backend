<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wellness_tips', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('category')->nullable(); // e.g., 'mindfulness', 'sleep', 'exercise'
            $table->string('icon')->nullable(); // emoji or icon name
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->string('language', 5)->default('en'); // en, ar, ku
            $table->timestamps();

            $table->index(['is_active', 'language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wellness_tips');
    }
};
