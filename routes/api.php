<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// School Location API endpoints
Route::get('/school-locations/active', function () {
    $locations = \App\Models\SchoolLocation::getActiveLocations();
    return response()->json([
        'success' => true,
        'locations' => $locations->map(function($location) {
            return $location->toMapData();
        })
    ]);
});

Route::post('/school-locations/validate', function (Request $request) {
    $request->validate([
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180'
    ]);
    
    $result = \App\Models\SchoolLocation::isWithinSchoolArea(
        $request->latitude,
        $request->longitude
    );
    
    if ($result['valid']) {
        return response()->json([
            'success' => true,
            'valid' => true,
            'message' => 'Lokasi valid',
            'location' => $result['location']->toMapData(),
            'distance' => $result['distance']
        ]);
    } else {
        $nearest = $result['nearest_location'];
        return response()->json([
            'success' => true,
            'valid' => false,
            'message' => 'Lokasi terlalu jauh dari sekolah',
            'nearest_location' => $nearest ? [
                'name' => $nearest->name,
                'distance' => $nearest->distance
            ] : null
        ]);
    }
});
