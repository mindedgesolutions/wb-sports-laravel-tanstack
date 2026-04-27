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
        Schema::create('mountain_trainings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->smallInteger('courses_count')->default(1);
            $table->smallInteger('duration')->default(1)->comment('Duration in days');
            $table->string('age_group_start')->nullable();
            $table->string('age_group_end')->nullable();
            $table->smallInteger('course_fee')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mountain_trainings');
    }
};
