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
        Schema::create('sp_sports_personnels', function (Blueprint $table) {
            $table->id();
            $table->string('sport');
            $table->string('name');
            $table->string('slug');
            $table->string('address')->nullable();
            $table->date('dob')->nullable();
            $table->string('contact_1')->nullable();
            $table->string('contact_2')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('show_order')->default(0);
            $table->foreignId('added_by')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sp_sports_personnels');
    }
};
