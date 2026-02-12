<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('household_dietary_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('dietary_tag_id')
                ->constrained('dietary_tags')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['household_id', 'dietary_tag_id']);

            $table->index(['household_id']);
            $table->index(['dietary_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('household_dietary_tags');
    }
};
