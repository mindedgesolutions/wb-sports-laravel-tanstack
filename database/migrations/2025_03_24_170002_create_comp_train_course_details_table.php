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
        Schema::create('comp_train_course_details', function (Blueprint $table) {
            $table->id();
            $table->string('course_type');
            $table->string('course_name');
            $table->string('course_slug')->nullable();
            $table->string('course_duration');
            $table->string('course_eligibility');
            $table->integer('course_fees')->default(0);
            $table->string('organisation')->comment('1.services 2.sports');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comp_train_course_details');
    }
};
