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
        Schema::table('centers', function (Blueprint $table) {
            // حذف عامود reliable
            $table->dropColumn('reliable');
            
            // إضافة عامود Verification status
            $table->string('verification_status')->default('Unverified')->after('sham_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('centers', function (Blueprint $table) {
            // حذف عامود Verification status
            $table->dropColumn('verification_status');
            
            // إعادة عامود reliable
            $table->boolean('reliable')->default(false)->after('password');
        });
    }
};
