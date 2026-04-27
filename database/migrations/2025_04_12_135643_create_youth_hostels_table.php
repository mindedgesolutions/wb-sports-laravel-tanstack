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
        Schema::create('youth_hostels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('address')->nullable();
            $table->string('phone_1')->nullable();
            $table->string('phone_2')->nullable();
            $table->string('email')->nullable();
            $table->text('accommodation')->nullable();
            $table->text('how_to_reach')->nullable();
            $table->string('railway_station')->nullable();
            $table->string('bus_stop')->nullable();
            $table->string('airport')->nullable();
            $table->text('road_network')->nullable();
            $table->string('remarks')->nullable();
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->string('hostel_img')->nullable();
            $table->foreignId('added_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('youth_hostels');
    }
};
