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
        Schema::create('fair_programmes', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->text('slug');
            $table->string('occurance');
            $table->longText('description')->nullable();
            $table->uuid('uuid')->unique();
            $table->foreignId('added_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->string('organisation');
            $table->string('cover_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fair_programmes');
    }
};
