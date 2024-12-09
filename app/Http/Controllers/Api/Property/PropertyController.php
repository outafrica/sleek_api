<?php

namespace App\Http\Controllers\Api\Property;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\AddPropertyRequest;
use App\Http\Requests\Property\DeletePropertyImageRequest;
use App\Http\Resources\Property\PropertyResource;
use App\Models\Property;
use App\Models\PropertyOwnershipAndLegalStatus;
use App\Models\PropertyPricingAndPayment;
use App\Traits\ImagesTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyController extends Controller
{
    use ImagesTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // get record limit per page
        $recordsPerPage = $request->per_page ?? 15;

        // Get the current page number, default to 1 if not present
        $page = $request->page ?? 1;

        // Generate a unique cache key for each page
        $cacheKey = 'properties_paginated_page_' . $page . '_per_page_' . $recordsPerPage;

        // Cache the paginated data for 1 minutes (60 seconds)
        $properties = Cache::tags(['feature-flags'])->remember($cacheKey, 60, function () use ($recordsPerPage) {
            return Property::with(['ownershipAndLegalStatus', 'pricingAndPayment'])->orderBy('created_at', 'desc')->paginate($recordsPerPage); // Fetch from DB if not cached
        });

        // Return a paginated resource collection
        return response()->json([
            'message' => 'Properties successfully fetched.',
            'data' => PropertyResource::collection($properties)->response()->getData(true),
        ], 200);
        //  return response()->json(['success' => true, 'message' => "Feature flags retrieved successfully", 'data' => PropertyResource::collection($property)->response()->getData(true)], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AddPropertyRequest $request)
    {
        try {
            // Use a database transaction to ensure all related records are created atomically
            $property = DB::transaction(function () use ($request) {
                // Handle file uploads
                $titleDeedPath = $request->input('title_deed') ? $this->processBase64Images($request->input('title_deed'), 'property/deeds') : null;
                $encumbranceCertificatePath = $request->input('encumbrance_certificate') ? $this->processBase64Images($request->input('encumbrance_certificate'), 'property/encumbrance/certificates') : null;
                $approvalDocsPath = $request->input('approval_docs') ? $this->processBase64Images($request->input('approval_docs'), 'property/aprroval/docs') : null;
                $featureImages = $this->processBase64Images($request->input('feature_images'), 'property/feature');

                // Create the property
                $property = Property::create([
                    'name' => $request->input('name'),
                    'plot_number' => $request->input('plot_number'),
                    'address' => $request->input('address'),
                    'closest_landmark' => $request->input('closest_landmark'),
                    'size' => $request->input('size'),
                    'measurement_unit' => $request->input('measurement_unit'),
                    'property_type' => $request->input('property_type'),
                    'feature_images' => $featureImages,

                ]);

                // Create ownership and legal status
                PropertyOwnershipAndLegalStatus::create([
                    'property_id' => $property->id,
                    'owner_name' => $request->input('owner_name'),
                    'owner_contact' => $request->input('owner_contact'),
                    'owner_email' => $request->input('owner_email'),
                    'title_deed' => $titleDeedPath,
                    'encumbrance_certificate' => $encumbranceCertificatePath,
                    'approval_docs' => $approvalDocsPath,
                ]);

                // Create pricing and payment details
                PropertyPricingAndPayment::create([
                    'property_id' => $property->id,
                    'sale_price' => $request->input('sale_price'),
                    'payment_methods' => $request->input('payment_methods'),
                    'associated_costs' => $request->input('associated_costs'),
                    'negotiation_potential' => $request->input('negotiation_potential'),
                    'negotiation_cap' => $request->input('negotiation_cap'),
                ]);

                return $property;
            });
            // Return a response
            return response()->json([
                'message' => 'Property successfully created.',
                'data' => new PropertyResource($property->load(['ownershipAndLegalStatus', 'pricingAndPayment'])),
            ], 200);
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            // Return a response
            return response()->json([
                'message' => 'Unable to create property, try again later!',
                'data' => array(),
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Property $property)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Property $property)
    {
        try {
            // get feature images
            $featureImages = $property->feature_images;

            // check if feature image is empty and none uploaded
            if ((empty($featureImages) && !$request->feature_images)) {
                return response()->json([
                    'message' => 'Feature image required for this property.',
                    'data' => array(),
                ], 422);
            }

            // Use a database transaction to ensure all related records are updated atomically
            DB::transaction(function () use ($property, $request) {

                // if image is there, process image the merge new vlaues for feature iamges
                if ($request->feature_images) {
                    $featureImages = array_unique(array_merge($this->processBase64Images($request->feature_images, 'property/feature'), $property->feature_images));
                }

                // prepare property data
                $property_data = $request->only([
                    'name',
                    'address',
                    'size',
                    'measurement_unit',
                    'property_type',
                    'plot_number'
                ]);

                // Append feature_images directly
                $property_data['feature_images'] = $featureImages;

                // Update the property fields
                $property->update($property_data);

                // Update ownership and legal status
                if ($ownership = $property->ownershipAndLegalStatus) {
                    $ownershipData = $request->only([
                        'owner_name',
                        'owner_contact',
                        'owner_email'
                    ]);

                    // Handle file uploads for ownership documents
                    if ($request->input('title_deed')) {
                        $ownershipData['title_deed'] =  array_unique(array_merge($this->processBase64Images($request->input('title_deed'), 'property/deeds'), $ownership->title_deed));
                    }
                    if ($request->input('encumbrance_certificate')) {
                        $ownershipData['encumbrance_certificate'] = array_unique(array_merge($this->processBase64Images($request->input('encumbrance_certificate'), 'property/encumbrance/certificates'), $ownership->encumbrance_certificate));
                    }
                    if ($request->input('approval_docs')) {
                        $ownershipData['approval_docs'] = array_unique(array_merge($this->processBase64Images($request->input('approval_docs'), 'property/aprroval/docs'), $ownership->approval_docs));
                    }

                    $ownership->update($ownershipData);
                }

                // Update pricing and payment details
                if ($pricing = $property->pricingAndPayment) {
                    $pricing->update($request->only([
                        'sale_price',
                        'payment_methods',
                        'associated_costs',
                        'negotiation_potential',
                        'negotiation_cap'
                    ]));
                }
            });

            // Return updated resource
            return response()->json([
                'message' => 'Property successfully updated.',
                'data' => new PropertyResource($property->load(['ownershipAndLegalStatus', 'pricingAndPayment'])),
            ], 200);
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            // Return a response
            return response()->json([
                'message' => 'Unable to update property, try again later!',
                'data' => array(),
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property)
    {
        // Use a database transaction to ensure all related records are deleted atomically
        DB::transaction(function () use ($property) {
            // Delete related ownership and legal status
            if ($property->ownershipAndLegalStatus) {
                $property->ownershipAndLegalStatus->delete();
            }

            // Delete related pricing and payment
            if ($property->pricingAndPayment) {
                $property->pricingAndPayment->delete();
            }

            // Delete the property itself
            $property->delete();
        });

        // Return a success response
        return response()->json([
            'message' => 'Property and its associated data successfully deleted.',
            'data' => array(),
        ], 200);
    }

    public function deletePropertyImages(DeletePropertyImageRequest $request, $propertyId)
    {
        // Fetch the property
        $property = Property::with('ownershipAndLegalStatus')->findOrFail($propertyId);
        $images = [];
        try {
            if ($request->type == 'feature') {
                $images = $this->deleteImages($property->feature_images, $request->images);
                $property->update(['feature_images' => $images]);
            } else {
                if ($request->type == 'deed') {
                    $images = $this->deleteImages($property->ownershipAndLegalStatus->title_deed, $request->images);
                    $property->ownershipAndLegalStatus()->update(['title_deed' => $images]);
                } else if ($request->type == 'approval') {
                    $images = $this->deleteImages($property->ownershipAndLegalStatus->approval_docs, $request->images);
                    $property->ownershipAndLegalStatus()->update(['approval_docs' => $images]);
                } else if ($request->type == 'certificate') {
                    $images = $this->deleteImages($property->ownershipAndLegalStatus->encumbrance_certificate, $request->images);
                    $property->ownershipAndLegalStatus()->update(['encumbrance_certificate' => $images]);
                }
            }

            return response()->json([
                'message' => 'Image(s) deleted successfully.',
                'data' => array(),
            ], 200);
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return response()->json([
                'message' => 'Unable to delete image(s)',
                'data' => array(),
            ], 400);
        }
    }
}
