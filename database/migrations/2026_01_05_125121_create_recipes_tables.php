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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->enum('type', ['petit-déjeuner', 'entrée', 'plat principal', 'dessert', 'collation', 'boisson', 'autre']) ->default('plat principal');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_ai_generated')->default(false);
            $table->string('source_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
