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
        Schema::create('sp_photo_galleries', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['photo', 'amphan'])->default('photo');
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('cover_img')->nullable();
            $table->date('event_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sp_photo_galleries');
    }
};
