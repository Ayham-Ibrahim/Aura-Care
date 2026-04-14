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
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('reservation_id')->after('center_id')->constrained('reservations')->cascadeOnDelete();
            $table->unique(['user_id', 'reservation_id'], 'user_reservation_review_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique('user_reservation_review_unique');
            $table->dropForeign(['reservation_id']);
            $table->dropColumn('reservation_id');
        });
    }
};
