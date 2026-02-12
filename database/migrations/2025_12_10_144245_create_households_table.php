<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('households', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->string('plan_status')->default('free');
            $table->timestamp('premium_ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->boolean('is_lifetime_premium')->default(false);

            $table->integer('week_start_day')->default(1);
            $table->boolean('is_joint_custody')->default(false);
            $table->foreignId('linked_household_id')
                ->nullable()
                ->constrained('households')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique('linked_household_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('households');
    }
};
