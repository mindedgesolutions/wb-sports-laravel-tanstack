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
        Schema::create('sp_players_achievements', function (Blueprint $table) {
            $table->id();
            $table->string('sport');
            $table->string('name');
            $table->string('slug')->comment('sport + name');
            $table->text('description');
            $table->date('achievement_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sp_players_achievements');
    }
};
