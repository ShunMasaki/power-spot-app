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
        Schema::create('spot_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spot_id')->constrained()->onDelete('cascade');
            $table->foreignId('benefit_type_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('rating')->comment('1〜5段階の評価');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spot_benefits');
    }
};
