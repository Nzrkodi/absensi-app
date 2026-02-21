<?php

namespace App\Services;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HolidayDetectionService
{
    /**
     * Check if a date is holiday (weekend + national holidays)
     */
    public function isHoliday($date): bool
    {
        $carbonDate = Carbon::parse($date);
        
        // Check if weekend
        if ($this->isWeekend($carbonDate)) {
            return true;
        }
        
        // Check if national holiday
        if ($this->isNationalHoliday($carbonDate)) {
            return true;
        }
        
        // Check manual holidays in database (use isManualHoliday to avoid circular dependency)
        return Holiday::isManualHoliday($date);
    }
    
    /**
     * Check if date is weekend (Saturday or Sunday)
     */
    public function isWeekend(Carbon $date): bool
    {
        // TEMPORARY: Disable weekend check for testing
        // Uncomment line below to enable weekend detection
        // return in_array($date->dayOfWeek, [0, 6]); // 0 = Sunday, 6 = Saturday
        
        return false; // Disabled for testing
    }
    
    /**
     * Check if date is national holiday using API
     */
    public function isNationalHoliday(Carbon $date): bool
    {
        $holidays = $this->getNationalHolidays($date->year);
        
        return in_array($date->format('Y-m-d'), $holidays);
    }
    
    /**
     * Get national holidays for a year from API with caching
     */
    public function getNationalHolidays(int $year): array
    {
        $cacheKey = "national_holidays_{$year}";
        
        return Cache::remember($cacheKey, now()->addDays(30), function () use ($year) {
            try {
                // Try harilibur.id API first (free, Indonesia specific)
                $response = Http::timeout(10)->get("https://api-harilibur.vercel.app/api", [
                    'year' => $year
                ]);
                
                if ($response->successful()) {
                    $holidays = [];
                    foreach ($response->json() as $holiday) {
                        if (isset($holiday['holiday_date'])) {
                            $holidays[] = $holiday['holiday_date'];
                        }
                    }
                    
                    Log::info("Fetched {count} national holidays for year {$year}", [
                        'count' => count($holidays),
                        'year' => $year
                    ]);
                    
                    return $holidays;
                }
                
                // Fallback to calendarific API if harilibur.id fails
                return $this->fetchFromCalendarific($year);
                
            } catch (\Exception $e) {
                Log::error("Failed to fetch national holidays: " . $e->getMessage());
                
                // Return empty array if all APIs fail
                return [];
            }
        });
    }
    
    /**
     * Fallback API - Calendarific (requires API key)
     */
    private function fetchFromCalendarific(int $year): array
    {
        $apiKey = config('services.calendarific.api_key');
        
        if (!$apiKey) {
            return [];
        }
        
        try {
            $response = Http::timeout(10)->get("https://calendarific.com/api/v2/holidays", [
                'api_key' => $apiKey,
                'country' => 'ID',
                'year' => $year,
                'type' => 'national'
            ]);
            
            if ($response->successful()) {
                $holidays = [];
                $data = $response->json();
                
                if (isset($data['response']['holidays'])) {
                    foreach ($data['response']['holidays'] as $holiday) {
                        if (isset($holiday['date']['iso'])) {
                            $holidays[] = $holiday['date']['iso'];
                        }
                    }
                }
                
                return $holidays;
            }
        } catch (\Exception $e) {
            Log::error("Calendarific API failed: " . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * Get weekend settings (which days are considered weekend)
     */
    private function getWeekendSettings(): array
    {
        // Default: Saturday (6) and Sunday (0)
        // You can make this configurable via settings table
        return [0, 6]; // 0 = Sunday, 6 = Saturday
    }
    
    /**
     * Auto-create weekend holidays for a year
     */
    public function createWeekendHolidays(int $year): int
    {
        $weekends = [];
        $startDate = Carbon::create($year, 1, 1);
        $endDate = Carbon::create($year, 12, 31);
        
        $current = $startDate->copy();
        
        // Collect all weekend dates first
        while ($current->lte($endDate)) {
            if ($this->isWeekend($current)) {
                $weekends[] = [
                    'name' => $current->dayOfWeek === 0 ? 'Minggu' : 'Sabtu',
                    'date' => $current->format('Y-m-d'),
                    'type' => 'weekend',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            $current->addDay();
        }
        
        if (empty($weekends)) {
            return 0;
        }
        
        // Get existing weekend holidays for this year to avoid duplicates
        $existingDates = Holiday::whereYear('date', $year)
            ->where('type', 'weekend')
            ->pluck('date')
            ->map(function($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })
            ->toArray();
        
        // Filter out existing dates
        $newWeekends = array_filter($weekends, function($weekend) use ($existingDates) {
            return !in_array($weekend['date'], $existingDates);
        });
        
        if (empty($newWeekends)) {
            return 0;
        }
        
        // Insert in chunks to avoid memory issues
        $chunks = array_chunk($newWeekends, 50);
        $created = 0;
        
        foreach ($chunks as $chunk) {
            Holiday::insert($chunk);
            $created += count($chunk);
        }
        
        return $created;
    }
    
    /**
     * Auto-create national holidays for a year
     */
    public function createNationalHolidays(int $year): int
    {
        $holidays = $this->getNationalHolidays($year);
        $created = 0;
        
        foreach ($holidays as $holidayDate) {
            // Check if holiday already exists
            $exists = Holiday::where('date', $holidayDate)->exists();
            
            if (!$exists) {
                // Try to get holiday name from API response
                $holidayName = $this->getHolidayName($holidayDate, $year);
                
                Holiday::create([
                    'name' => $holidayName,
                    'date' => $holidayDate,
                    'type' => 'national',
                    'is_active' => true
                ]);
                $created++;
            }
        }
        
        return $created;
    }
    
    /**
     * Get holiday name for a specific date
     */
    private function getHolidayName(string $date, int $year): string
    {
        try {
            $response = Http::timeout(5)->get("https://api-harilibur.vercel.app/api", [
                'year' => $year
            ]);
            
            if ($response->successful()) {
                foreach ($response->json() as $holiday) {
                    if (isset($holiday['holiday_date']) && $holiday['holiday_date'] === $date) {
                        return $holiday['holiday_name'] ?? 'Hari Libur Nasional';
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignore errors, use default name
        }
        
        return 'Hari Libur Nasional';
    }
    
    /**
     * Clear holiday cache
     */
    public function clearCache(?int $year = null): void
    {
        if ($year) {
            Cache::forget("national_holidays_{$year}");
        } else {
            // Clear all holiday caches
            for ($y = 2020; $y <= 2030; $y++) {
                Cache::forget("national_holidays_{$y}");
            }
        }
    }
}