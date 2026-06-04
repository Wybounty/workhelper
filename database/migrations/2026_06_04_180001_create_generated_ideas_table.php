<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_ideas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generation_id')
                ->constrained('idea_generations')
                ->cascadeOnDelete();
            $table->string('title');
            $table->string('application_type');
            $table->text('description');
            $table->text('why_useful');
            $table->string('development_duration');
            $table->text('recommended_stack');
            $table->string('business_model');
            $table->string('estimated_monthly_revenue');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_ideas');
    }
};
