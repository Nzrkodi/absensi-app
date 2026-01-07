<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentViolation extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'violation_type_id',
        'violation_date',
        'violation_time',
        'location',
        'description',
        'reported_by',
        'status',
        'resolution_notes'
    ];

    protected $casts = [
        'violation_date' => 'date',
        'violation_time' => 'datetime:H:i'
    ];

    /**
     * Relasi ke student
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relasi ke violation type
     */
    public function violationType()
    {
        return $this->belongsTo(ViolationType::class);
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('violation_date', $date);
    }

    /**
     * Scope untuk filter berdasarkan bulan
     */
    public function scopeByMonth($query, $month, $year = null)
    {
        $year = $year ?? date('Y');
        return $query->whereMonth('violation_date', $month)
                    ->whereYear('violation_date', $year);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'confirmed' => 'danger',
            'resolved' => 'success',
            default => 'secondary'
        };
    }

    /**
     * Get formatted violation datetime
     */
    public function getFormattedDateTimeAttribute()
    {
        $date = $this->violation_date->format('d/m/Y');
        $time = $this->violation_time ? $this->violation_time->format('H:i') : '';
        return $time ? "$date $time" : $date;
    }
}