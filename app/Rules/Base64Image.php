<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Base64Image implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Ensure the value is an array
        if (!is_array($value)) {
            $fail("The {$attribute} must be an array of base64-encoded images.");
            return;
        }

        // Loop through the array to check each base64-encoded image
        foreach ($value as $index => $image) {
            if (!preg_match('/^data:image\/(png|jpg|jpeg);base64,/', $image)) {
                $fail("Each item in {$attribute} must be a valid base64-encoded image (PNG, JPG, or JPEG).");
                return;
            }

            // Strip the base64 image header and try to decode it
            $imageData = preg_replace('/^data:image\/(png|jpg|jpeg);base64,/', '', $image);

            // Check if decoding the image data works
            if (base64_decode($imageData, true) === false) {
                $fail("Each item in {$attribute} must be a valid base64-encoded image (PNG, JPG, or JPEG).");
                return;
            }
        }
    }
}
