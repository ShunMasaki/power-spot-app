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
        // goshuin_images テーブルを更新
        Schema::table('goshuin_images', function (Blueprint $table) {
            $table->renameColumn('image_url', 'image_path');
            $table->string('original_filename')->nullable()->after('image_path');
        });

        // omikuji_images テーブルを更新
        Schema::table('omikuji_images', function (Blueprint $table) {
            $table->renameColumn('image_url', 'image_path');
            $table->string('original_filename')->nullable()->after('image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // goshuin_images テーブルを元に戻す
        Schema::table('goshuin_images', function (Blueprint $table) {
            $table->dropColumn('original_filename');
            $table->renameColumn('image_path', 'image_url');
        });

        // omikuji_images テーブルを元に戻す
        Schema::table('omikuji_images', function (Blueprint $table) {
            $table->dropColumn('original_filename');
            $table->renameColumn('image_path', 'image_url');
        });
    }
};
