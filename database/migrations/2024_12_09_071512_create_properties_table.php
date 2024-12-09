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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->string('closest_landmark');
            $table->string('plot_number')->unique();
            $table->json('feature_images');
            $table->decimal('size', 10, 2);  // Size of the property
            $table->enum('measurement_unit', ['km', 'miles']);
            $table->enum('property_type', ['residential', 'commercial', 'industrial', 'agricultural', 'mixed-use'])->default('residential');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
