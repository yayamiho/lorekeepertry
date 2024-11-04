<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('carousel', function (Blueprint $table) {
            $table->integer('sort')->unsigned()->default(0);
            $table->boolean('is_visible')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('carousel', function (Blueprint $table) {
            $table->dropColumn('sort');
            $table->dropColumn('is_visible');
        });
    }
};
