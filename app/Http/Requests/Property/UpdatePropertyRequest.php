<?php

namespace App\Http\Requests\Property;

use App\Rules\Base64Image;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
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
          'name' => 'sometimes|string|max:255',
          'address' => 'sometimes|string|max:255',
          'plot_number' => ['sometimes','string','max:255', Rule::unique('properties')->ignore($this->route('id')),],
          'size' => 'sometimes|numeric|min:0',
          'measurement_unit' => 'sometimes|in:km,miles',
          'property_type' => 'sometimes|in:residential,commercial,industrial,agricultural,mixed-use',
          'feature_images' => ['sometimes', new Base64Image],


          // Ownership rules
          'owner_name' => 'sometimes|string|max:255',
          'owner_contact' => 'sometimes|string|max:20',
          'owner_email' => 'sometimes|email|max:255',
          'title_deed' => ['sometimes', new Base64Image],
          'encumbrance_certificate' => ['sometimes', new Base64Image],
          'approval_docs' => ['sometimes', new Base64Image],

          // Pricing and Payment rules
          'sale_price' => 'sometimes|numeric|min:0',
          'payment_methods' => 'sometimes|in:cash,bank_transfer,demand_draft,mortgage,loan',
          'associated_costs' => 'sometimes|in:stamp_duty,registration_fees,maintenance_fees, no_costs',
          'negotiation_potential' => 'sometimes|boolean',
          'negotiation_cap' => 'sometimes_if:negotiation_potential,true|numeric|min:0|max:100',
      ];
    }
}
