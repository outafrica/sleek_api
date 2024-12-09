<?php

use App\Http\Controllers\Api\Property\PropertyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// api resource to handle properties
Route::apiResource('/property', PropertyController::class)->missing(function () {
    return response()->json([
        'message' => 'Resource not found.',
        'data' => array(),
    ], 404);
});
Route::post('/property/delete-images/{id}', [PropertyController::class, 'deletePropertyImages']);

