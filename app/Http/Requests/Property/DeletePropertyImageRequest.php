<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;

class DeletePropertyImageRequest extends FormRequest
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
            'images' => 'required|array', // Ensure it's an array
            'images.*' => ['string'],          // Ensure each element in the array is a string
            'type' => 'required|in:deed,feature,approval,certificate' //define the type of image to be deleted
        ];
    }
}
