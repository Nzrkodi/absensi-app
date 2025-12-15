<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime:H:i',
        'clock_out' => 'datetime:H:i',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'present' => '<span class="badge bg-success">Hadir</span>',
            'late' => '<span class="badge bg-warning">Terlambat</span>',
            'absent' => '<span class="badge bg-danger">Tidak Hadir</span>',
            'permission' => '<span class="badge bg-info">Izin</span>',
            'sick' => '<span class="badge bg-secondary">Sakit</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-light">Unknown</span>';
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
        return $this->clock_in && !$this->clock_out && $this->status === 'present';
    }

    public function canClockIn()
    {
        return !$this->clock_in && in_array($this->status, ['present', 'late']);
    }
}