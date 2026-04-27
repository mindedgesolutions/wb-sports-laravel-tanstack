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
        Schema::create('comp_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distict_id')->constrained('districts');
            $table->string('yctc_name');
            $table->string('yctc_code')->nullable();
            $table->string('center_category')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('address_line_3')->nullable();
            $table->string('city')->nullable();
            $table->string('pincode')->nullable();
            $table->string('center_incharge_name')->nullable();
            $table->string('center_incharge_mobile')->nullable();
            $table->string('center_incharge_email')->nullable();
            $table->string('center_owner_name')->nullable();
            $table->string('center_owner_mobile')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('added_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comp_centers');
    }
};
