<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyOwnershipAndLegalStatus extends Model
{
    protected $fillable = [
        'property_id', 
        'owner_name', 
        'owner_contact', 
        'owner_email', 
        'title_deed', 
        'encumbrance_certificate', 
        'approval_docs'
    ];

    protected $casts = [
        'title_deed' => 'array',  
        'encumbrance_certificate' => 'array',  
        'approval_docs' => 'array',  
    ];
}
