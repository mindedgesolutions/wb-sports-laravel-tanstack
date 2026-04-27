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
        Schema::create('dpop_proofs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('jti', 200)->unique(); // unique prevents reuse
            $table->string('jkt', 128)->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dpop_proofs');
    }
};
