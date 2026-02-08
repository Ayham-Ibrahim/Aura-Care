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
        Schema::create('centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->decimal('location_h', 10, 7);
            $table->decimal('location_v', 10, 7);
            $table->string('phone');
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password');
            $table->boolean('reliable')->default(false);
            $table->string('owner_name');
            $table->string('owner_number');
            $table->decimal('rating', 3, 2)->default(0);
            $table->string('sham_image')->nullable();
            $table->string('sham_code')->nullable();
            // $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centers');
    }
};
