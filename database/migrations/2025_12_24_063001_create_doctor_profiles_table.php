<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('specialization');
            $table->string('license_number')->unique();
            $table->text('bio')->nullable();
            $table->json('qualifications')->nullable();
            $table->integer('experience_years')->default(0);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->json('verification_documents')->nullable();
            $table->json('languages')->nullable();
            $table->json('consultation_types')->nullable();
            $table->timestamps();

            $table->index(['is_verified', 'specialization']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_profiles');
    }
};
