<?php

namespace App\Http\Requests\Property;

use App\Rules\Base64Image;
use Illuminate\Foundation\Http\FormRequest;

class AddPropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Property rules
            'name' => 'required|string|max:255',
            'plot_number' => 'required|string|max:255|unique:properties,plot_number',
            'address' => 'required|string|max:255',
            'closest_landmark' => 'required|string|max:255',
            'size' => 'required|numeric|min:0',
            'feature_images' => ['required', new Base64Image],
            'measurement_unit' => 'required|in:km,miles',
            'property_type' => 'required|in:residential,commercial,industrial,agricultural,mixed-use',

            // Ownership rules
            'owner_name' => 'required|string|max:255',
            'owner_contact' => 'required|string|max:20',
            'owner_email' => 'required|email|max:255',
            'title_deed' => ['sometimes', new Base64Image],
            'encumbrance_certificate' => ['sometimes', new Base64Image],
            'approval_docs' => ['sometimes', new Base64Image],

            // Pricing and Payment rules
            'sale_price' => 'required|numeric|min:0',
            'payment_methods' => 'required|in:cash,bank_transfer,demand_draft,mortgage,loan',
            'associated_costs' => 'required|in:stamp_duty,registration_fees,maintenance_fees, no_costs',
            'negotiation_potential' => 'required|boolean',
            'negotiation_cap' => 'required_if:negotiation_potential,true|numeric|min:0|max:100',
        ];
    }
}
