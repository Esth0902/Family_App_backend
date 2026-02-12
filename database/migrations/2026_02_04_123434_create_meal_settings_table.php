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
        Schema::create('meal_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained('households')->cascadeOnDelete();

            $table->integer('poll_day')->default(5);
            $table->time('poll_time')->default('10:00');
            $table->integer('poll_duration')->default(24);

            $table->boolean('auto_generate_shopping_list')->default(true);

            $table->integer('max_votes_per_user')->default(3);
            $table->timestamps();

            $table->unique('household_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_settings');
    }
};
