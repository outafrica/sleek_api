<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

trait ImagesTrait
{

    private function processBase64Images( array $images, string $path = 'property/images'): array
    {
        return array_map(function ($image) use ($path) {
            // Extract the image extension from the base64 data
            preg_match('/data:image\/(\w+);base64,/', $image, $matches);
            $extension = $matches[1] ?? 'png'; // Default to 'png' if no match

            // Generate a unique filename for the image
            $filename = uniqid('image_', true) . '.' . $extension;

            // Decode the base64 image data
            $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $image));

            // Construct the full path in the public directory
            $publicPath = public_path("{$path}/{$filename}");

            // Ensure the directory exists
            if (!file_exists(dirname($publicPath))) {
                mkdir(dirname($publicPath), 0777, true);
            }

            // Save the file to the public directory
            file_put_contents($publicPath, $imageData);

            // Generate the full publicly accessible URL for the image
            $fullImagePath = asset("{$path}/{$filename}");

            return $fullImagePath;
        }, $images);
    }

    function deleteImages(array $storedImages, array $imagesToDelete): array
    {
        // Filter out images that match the paths in $imagesToDelete
        $updatedImages = array_filter($storedImages, function ($image) use ($imagesToDelete) {
            // Check if the image is in the list of images to delete
            if (in_array($image, $imagesToDelete)) {
                // Convert the public URL to the full file path
                $publicPath = str_replace(asset('/'), '', $image); // Remove app URL from the image path
                $fullPath = public_path($publicPath);

                // Delete the image file if it exists
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }

                // Exclude this image from the updated list
                return false;
            }

            // Keep this image in the updated list
            return true;
        });

        // Return the updated images as array
        return array_values($updatedImages);
    }
}
