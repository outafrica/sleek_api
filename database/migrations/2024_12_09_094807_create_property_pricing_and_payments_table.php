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
        Schema::create('property_pricing_and_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade'); // Relates to the properties table
            $table->decimal('sale_price', 15, 2); // Sale price for reference
            $table->enum('payment_methods', ['cash', 'bank_transfer', 'demand_draft', 'mortgage', 'loan'])->default('demand_draft');
            $table->boolean('negotiation_potential')->default(false);
            $table->decimal('negotiation_cap', 5, 2)->default(0); // As percentage (e.g., 10.00 for 10%)
            $table->enum('associated_costs', ['stamp_duty', 'registration_fees', 'maintenance_fees', 'no_costs'])->default('no_costs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_pricing_and_payments');
    }
};
