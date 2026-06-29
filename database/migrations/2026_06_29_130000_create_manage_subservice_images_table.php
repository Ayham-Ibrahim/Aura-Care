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
        Schema::create('manage_subservice_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manage_subservice_id')
                ->constrained('manage_subservices')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manage_subservice_images');
    }
};
