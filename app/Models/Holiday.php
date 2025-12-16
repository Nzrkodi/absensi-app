<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'description',
        'type',
        'is_active'
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean'
    ];

    /**
     * Check if a given date is a holiday
     */
    public static function isHoliday($date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');
        
        return self::where('date', $date)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Check if today is a holiday
     */
    public static function isTodayHoliday()
    {
        return self::isHoliday(Carbon::today('Asia/Makassar'));
    }

    /**
     * Get holiday by date
     */
    public static function getHoliday($date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');
        
        return self::where('date', $date)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get upcoming holidays
     */
    public static function getUpcoming($limit = 5)
    {
        return self::where('date', '>=', Carbon::today('Asia/Makassar'))
            ->where('is_active', true)
            ->orderBy('date', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get holidays in a date range
     */
    public static function getInRange($startDate, $endDate)
    {
        return self::whereBetween('date', [$startDate, $endDate])
            ->where('is_active', true)
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Auto-create weekend holidays for a year
     */
    public static function createWeekendHolidays($year = null)
    {
        $year = $year ?: Carbon::now()->year;
        $startDate = Carbon::createFromDate($year, 1, 1);
        $endDate = Carbon::createFromDate($year, 12, 31);
        
        $weekends = [];
        $current = $startDate->copy();
        
        while ($current->lte($endDate)) {
            if ($current->isWeekend()) {
                $weekends[] = [
                    'name' => $current->isSaturday() ? 'Sabtu' : 'Minggu',
                    'date' => $current->format('Y-m-d'),
                    'description' => 'Hari libur weekend',
                    'type' => 'weekend',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            $current->addDay();
        }
        
        // Insert in chunks to avoid memory issues
        $chunks = array_chunk($weekends, 100);
        foreach ($chunks as $chunk) {
            self::insertOrIgnore($chunk);
        }
        
        return count($weekends);
    }

    /**
     * Get type badge for display
     */
    public function getTypeBadgeAttribute()
    {
        $badges = [
            'national' => '<span class="badge bg-danger">Nasional</span>',
            'school' => '<span class="badge bg-primary">Sekolah</span>',
            'weekend' => '<span class="badge bg-secondary">Weekend</span>',
        ];

        return $badges[$this->type] ?? '<span class="badge bg-light">Unknown</span>';
    }
}