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
        Schema::create('manage_subservices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('subservice_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->decimal('price')->nullable();
            $table->boolean('is_active')->default(false);


            // Points feature for the sub-service
            $table->boolean('activating_points')->default(false);
            $table->integer('points')->default(0);
            $table->date('from')->nullable();
            $table->date('to')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manage_subservices');
    }
};
