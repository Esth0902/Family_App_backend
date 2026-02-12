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
        Schema::ensureVectorExtensionExists();
        Schema::create('dietary_tags', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['diet', 'allergen', 'dislike', 'restriction', 'cuisine_rule']);
            $table->string('key', 120);
            $table->string('label', 255);
            $table->boolean('is_system')->default(true);
            $table->foreignId('created_by_household_id')
                ->nullable()
                ->constrained('households')
                ->nullOnDelete();
            $table->vector('embedding', dimensions: 512)->nullable();
            $table->timestamps();

            $table->unique(['type','key']);
        });

        DB::statement("CREATE INDEX dietary_tags_embedding_hnsw ON dietary_tags USING hnsw (embedding vector_cosine_ops)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS dietary_tags_embedding_hnsw");
        Schema::dropIfExists('dietary_tags');
    }
};
