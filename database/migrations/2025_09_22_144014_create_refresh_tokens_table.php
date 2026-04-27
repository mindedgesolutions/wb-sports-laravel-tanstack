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
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('token_hash');
            $table->string('family_id');
            $table->string('organisation');
            $table->string('user_agent')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->timestamp('expires_at');
            $table->boolean('revoked')->default(false);
            $table->string('access_token_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};
