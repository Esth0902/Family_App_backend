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
        Schema::create('meal_polls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->enum('status', ['open', 'closed', 'validated'])->default('open');
            $table->timestamps();
        });

        Schema::create('meal_poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_poll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['meal_poll_id', 'recipe_id']);
        });

        Schema::create('meal_poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_poll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meal_poll_option_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'meal_poll_option_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_polls');
    }
};
