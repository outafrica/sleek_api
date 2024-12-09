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
        Schema::create('property_ownership_and_legal_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade'); // Relates to the properties table
            $table->string('owner_name');
            $table->string('owner_contact');
            $table->string('owner_email')->nullable();
            $table->json('title_deed')->nullable(); // File path for title deed
            $table->json('encumbrance_certificate')->nullable(); // File path for encumbrance certificate
            $table->json('approval_docs')->nullable(); // File path for approval docs
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_ownership_and_legal_statuses');
    }
};
