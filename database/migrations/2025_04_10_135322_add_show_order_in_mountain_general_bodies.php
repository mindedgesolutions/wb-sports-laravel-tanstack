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
        Schema::table('mountain_general_bodies', function (Blueprint $table) {
            $table->integer('show_order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mountain_general_bodies', function (Blueprint $table) {
            $table->dropColumn('show_order');
        });
    }
};
