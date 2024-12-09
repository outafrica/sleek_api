<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    //

    protected $fillable = [
        'name',
        'address', 
        'closest_landmark', 
        'plot_number',
        'size', 
        'feature_images',
        'measurement_unit', 
        'property_type', 
    ];

    protected $casts = [
        'feature_images' => 'array',  // This will automatically cast feature_images to/from JSON
    ];

    // Property Model (Property.php)
    public function ownershipAndLegalStatus()
    {
        return $this->hasOne(PropertyOwnershipAndLegalStatus::class);
    }

    public function pricingAndPayment()
    {
        return $this->hasOne(PropertyPricingAndPayment::class);
    }
}
