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
        Schema::table('fair_programmes', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            $table->date('event_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fair_programmes', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropColumn('event_date');
        });
    }
};
