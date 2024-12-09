<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyPricingAndPayment extends Model
{
    //

    protected $fillable = [
        'property_id',
        'sale_price',
        'payment_methods',
        'associated_costs',
        'negotiation_potential',
        'negotiation_cap'
    ];
}
