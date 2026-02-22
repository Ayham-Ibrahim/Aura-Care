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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained('centers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->decimal('deposit_amount', 10, 2)->nullable();
            $table->string('status')->default('pending'); // pending, confirmed,processing ,cancelled, completed
            $table->dateTime('date')->nullable();
            $table->string('payment_image')->nullable();
            $table->string('cancellation_image')->nullable();
            $table->text('reason_for_cancellation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
