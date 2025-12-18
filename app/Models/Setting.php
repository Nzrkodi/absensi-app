<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description'
    ];

    /**
     * Get setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return self::castValue($setting->value, $setting->type);
    }

    /**
     * Set setting value
     */
    public static function set($key, $value, $type = 'string', $description = null)
    {
        \Log::info('Setting::set called', [
            'key' => $key,
            'value' => $value,
            'type' => $type,
            'description' => $description
        ]);

        $result = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description
            ]
        );

        \Log::info('Setting::set result', [
            'key' => $key,
            'saved_value' => $result->value,
            'model_id' => $result->id,
            'was_recently_created' => $result->wasRecentlyCreated
        ]);

        return $result;
    }

    /**
     * Cast value based on type
     */
    private static function castValue($value, $type)
    {
        switch ($type) {
            case 'integer':
                return (int) $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'time':
            case 'string':
            default:
                return $value;
        }
    }

    /**
     * Get all attendance settings
     */
    public static function getAttendanceSettings()
    {
        return [
            'school_start_time' => self::get('school_start_time', '07:00'),
            'late_tolerance_minutes' => self::get('late_tolerance_minutes', 15),
            'auto_absent_time' => self::get('auto_absent_time', '15:00'),
            'allow_early_clockin' => self::get('allow_early_clockin', true),
        ];
    }
}