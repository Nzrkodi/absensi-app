<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolLocation;
use Illuminate\Http\Request;

class SchoolLocationController extends Controller
{
    public function index()
    {
        $locations = SchoolLocation::orderBy('name')->paginate(10);
        return view('admin.school-locations.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.school-locations.create');
    }

    public function store(Request $request)
    {
        // Simple debug - akan muncul di log
        \Log::info('=== STORE METHOD CALLED ===');
        
        // Debug: Log request data
        \Log::info('Create School Location Request', [
            'request_data' => $request->all()
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|string|regex:/^-?[0-9]+\.?[0-9]*$/',
            'longitude' => 'required|string|regex:/^-?[0-9]+\.?[0-9]*$/',
            'radius_meters' => 'required|integer|min:10|max:1000',
            'description' => 'nullable|string|max:500',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'nullable|boolean'
        ]);

        $createData = [
            'name' => $request->name,
            'latitude' => (float) $request->latitude,
            'longitude' => (float) $request->longitude,
            'radius_meters' => $request->radius_meters,
            'description' => $request->description,
            'color' => $request->color,
            'is_active' => $request->has('is_active')
        ];

        // Debug: Log create data
        \Log::info('Create Data', $createData);

        $location = SchoolLocation::create($createData);

        // Debug: Log result
        \Log::info('Create Result', [
            'success' => $location ? true : false,
            'created_model' => $location
        ]);

        return redirect()->route('admin.school-locations.index')
            ->with('success', 'Lokasi sekolah berhasil ditambahkan');
    }

    public function show(SchoolLocation $schoolLocation)
    {
        return view('admin.school-locations.show', compact('schoolLocation'));
    }

    public function edit(SchoolLocation $schoolLocation)
    {
        return view('admin.school-locations.edit', compact('schoolLocation'));
    }

    public function update(Request $request, SchoolLocation $schoolLocation)
    {
        // Simple debug - akan muncul di log
        \Log::info('=== UPDATE METHOD CALLED ===');
        
        // Debug: Log request data
        \Log::info('Update School Location Request', [
            'id' => $schoolLocation->id,
            'request_data' => $request->all()
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|string|regex:/^-?[0-9]+\.?[0-9]*$/',
            'longitude' => 'required|string|regex:/^-?[0-9]+\.?[0-9]*$/',
            'radius_meters' => 'required|integer|min:10|max:1000',
            'description' => 'nullable|string|max:500',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'nullable|boolean'
        ]);

        $updateData = [
            'name' => $request->name,
            'latitude' => (float) $request->latitude,
            'longitude' => (float) $request->longitude,
            'radius_meters' => $request->radius_meters,
            'description' => $request->description,
            'color' => $request->color,
            'is_active' => $request->has('is_active')
        ];

        // Debug: Log update data
        \Log::info('Update Data', $updateData);

        $result = $schoolLocation->update($updateData);

        // Debug: Log result
        \Log::info('Update Result', [
            'success' => $result,
            'updated_model' => $schoolLocation->fresh()
        ]);

        return redirect()->route('admin.school-locations.index')
            ->with('success', 'Lokasi sekolah berhasil diperbarui');
    }

    public function destroy(SchoolLocation $schoolLocation)
    {
        $schoolLocation->delete();
        
        return redirect()->route('admin.school-locations.index')
            ->with('success', 'Lokasi sekolah berhasil dihapus');
    }

    /**
     * API endpoint untuk mendapatkan lokasi aktif
     */
    public function getActiveLocations()
    {
        $locations = SchoolLocation::getActiveLocations();
        
        return response()->json([
            'success' => true,
            'locations' => $locations->map(function($location) {
                return $location->toMapData();
            })
        ]);
    }

    /**
     * API endpoint untuk validasi lokasi
     */
    public function validateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180'
        ]);

        $result = SchoolLocation::isWithinSchoolArea(
            $request->latitude, 
            $request->longitude
        );

        return response()->json([
            'success' => true,
            'valid' => $result['valid'],
            'location' => $result['location'] ?? null,
            'nearest_location' => $result['nearest_location'] ?? null,
            'distance' => $result['distance'] ?? null,
            'message' => $result['valid'] 
                ? 'Lokasi valid untuk absensi' 
                : 'Lokasi terlalu jauh dari area sekolah'
        ]);
    }
}