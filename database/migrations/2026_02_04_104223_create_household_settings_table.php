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
        Schema::create('household_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('household_id')->constrained()->cascadeOnDelete();

            $table->boolean('has_meals')->default(true);
            $table->boolean('has_shopping_list')->default(true);
            $table->boolean('has_tasks')->default(true);
            $table->boolean('has_budget')->default(true);
            $table->boolean('has_calendar')->default(true);

            $table->timestamps();

            $table->unique('household_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('household_settings');
    }
};
