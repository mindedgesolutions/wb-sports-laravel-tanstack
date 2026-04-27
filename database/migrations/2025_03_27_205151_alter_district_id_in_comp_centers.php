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
        Schema::table('comp_centers', function (Blueprint $table) {
            $table->renameColumn('distict_id', 'district_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comp_centers', function (Blueprint $table) {
            $table->renameColumn('district_id', 'distict_id');
        });
    }
};
