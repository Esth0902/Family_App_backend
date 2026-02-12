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
        Schema::create('shopping_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained('households')->cascadeOnDelete();
            $table->string('title')->nullable()->default('Ma liste de courses');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('shopping_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignID('shopping_list_id')->constrained('shopping_lists')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->nullable()->constrained('ingredients')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('quantity')->nullable();
            $table->string('unit')->nullable();
            $table->boolean('is_checked')->default(false);
            $table->boolean('is_manual_addition')->default(false);
            $table->timestamps();
            $table->unique(['shopping_list_id', 'ingredient_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopping_lists');
        Schema::dropIfExists('shopping_list_items');
    }
};
