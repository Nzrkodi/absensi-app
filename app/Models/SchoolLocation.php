<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'radius_meters',
        'is_active',
        'description',
        'color'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean'
    ];

    /**
     * Get all active locations
     */
    public static function getActiveLocations()
    {
        return self::where('is_active', true)->get();
    }

    /**
     * Check if coordinates are within any active location
     */
    public static function isWithinSchoolArea($latitude, $longitude)
    {
        $locations = self::getActiveLocations();
        
        foreach ($locations as $location) {
            $distance = self::calculateDistance(
                $latitude, 
                $longitude, 
                $location->latitude, 
                $location->longitude
            );
            
            if ($distance <= $location->radius_meters) {
                return [
                    'valid' => true,
                    'location' => $location,
                    'distance' => round($distance, 2)
                ];
            }
        }
        
        return [
            'valid' => false,
            'nearest_location' => self::getNearestLocation($latitude, $longitude),
            'distance' => null
        ];
    }

    /**
     * Get nearest location from coordinates
     */
    public static function getNearestLocation($latitude, $longitude)
    {
        $locations = self::getActiveLocations();
        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;
        
        foreach ($locations as $location) {
            $distance = self::calculateDistance(
                $latitude, 
                $longitude, 
                $location->latitude, 
                $location->longitude
            );
            
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $location;
                $nearest->distance = round($distance, 2);
            }
        }
        
        return $nearest;
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Earth's radius in meters
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) + 
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLon/2) * sin($dLon/2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }

    /**
     * Get location for JavaScript
     */
    public function toMapData()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'radius' => $this->radius_meters,
            'color' => $this->color,
            'description' => $this->description
        ];
    }
}