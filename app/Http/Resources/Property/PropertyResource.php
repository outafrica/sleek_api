<?php

namespace App\Http\Resources\Property;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'plot_number' => $this->plot_number,
            'address' => $this->address,
            'size' => $this->size,
            'measurement_unit' => $this->measurement_unit,
            'property_type' => $this->property_type,
            'feature_images' => $this->feature_images,
            'ownership_and_legal_status' => $this->whenLoaded('ownershipAndLegalStatus', function () {
                return [
                    'owner_name' => $this->ownershipAndLegalStatus->owner_name,
                    'owner_contact' => $this->ownershipAndLegalStatus->owner_contact,
                    'owner_email' => $this->ownershipAndLegalStatus->owner_email,
                    'title_deed' => $this->ownershipAndLegalStatus->title_deed,
                    'encumbrance_certificate' => $this->ownershipAndLegalStatus->encumbrance_certificate,
                    'approval_docs' => $this->ownershipAndLegalStatus->approval_docs,
                ];
            }),
            'pricing_and_payment' => $this->whenLoaded('pricingAndPayment', function () {
                return [
                    'sale_price' => $this->pricingAndPayment->sale_price,
                    'payment_methods' => $this->pricingAndPayment->payment_methods,
                    'associated_costs' => $this->pricingAndPayment->associated_costs,
                    'negotiation_potential' => $this->pricingAndPayment->negotiation_potential,
                    'negotiation_cap' => $this->pricingAndPayment->negotiation_cap,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
