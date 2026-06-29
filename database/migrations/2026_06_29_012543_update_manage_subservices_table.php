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
        Schema::table('manage_subservices', function (Blueprint $table) {
            // إضافة عمود الصورة
            $table->string('image')->nullable();
            $table->string('description')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manage_subservices', function (Blueprint $table) {
            $table->dropColumn(['image', 'description']);
        });
    }
};
