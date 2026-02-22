<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TeacherAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'notes',
        'clock_in_photo',
        'clock_out_photo',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_out_latitude',
        'clock_out_longitude',
        'location_verified',
        'distance_from_school'
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime:H:i',
        'clock_out' => 'datetime:H:i',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'hadir' => '<span class="badge bg-success">Hadir</span>',
            'alpha' => '<span class="badge bg-danger">Tidak Hadir</span>',
            'izin' => '<span class="badge bg-info">Izin</span>',
            'sakit' => '<span class="badge bg-secondary">Sakit</span>',
            // Legacy support (just in case)
            'present' => '<span class="badge bg-success">Hadir</span>',
            'late' => '<span class="badge bg-warning">Terlambat</span>',
            'absent' => '<span class="badge bg-danger">Tidak Hadir</span>',
            'permission' => '<span class="badge bg-info">Izin</span>',
            'sick' => '<span class="badge bg-secondary">Sakit</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-light text-dark">Unknown</span>';
    }

    public function getClockInTimeAttribute()
    {
        return $this->clock_in ? Carbon::parse($this->clock_in)->format('H:i') : null;
    }

    public function getClockOutTimeAttribute()
    {
        return $this->clock_out ? Carbon::parse($this->clock_out)->format('H:i') : null;
    }

    public function canClockOut()
    {
        return $this->clock_in && !$this->clock_out;
    }

    public function canClockIn()
    {
        return !$this->clock_in;
    }
    
    /**
     * Get work duration in hours
     */
    public function getWorkDurationAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return null;
        }
        
        $clockIn = Carbon::parse($this->clock_in);
        $clockOut = Carbon::parse($this->clock_out);
        
        return $clockIn->diffInMinutes($clockOut);
    }
    
    /**
     * Get formatted work duration
     */
    public function getFormattedWorkDurationAttribute()
    {
        $minutes = $this->work_duration;
        
        if (!$minutes) {
            return '-';
        }
        
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        return sprintf('%d jam %d menit', $hours, $mins);
    }
    
    /**
     * Get formatted work duration (method version)
     */
    public function getWorkDurationFormatted()
    {
        return $this->formatted_work_duration;
    }
}
