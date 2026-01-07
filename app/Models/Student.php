<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nisn',
        'class_id',
        'birth_place',
        'birth_date',
        'phone',
        'address',
        'status',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Relasi ke student violations
     */
    public function violations(): HasMany
    {
        return $this->hasMany(StudentViolation::class);
    }

    /**
     * Get total violation points for student
     */
    public function getTotalViolationPointsAttribute()
    {
        return $this->violations()
            ->join('violation_types', 'student_violations.violation_type_id', '=', 'violation_types.id')
            ->sum('violation_types.points');
    }

    /**
     * Get violations count by category
     */
    public function getViolationsByCategory($category)
    {
        return $this->violations()
            ->join('violation_types', 'student_violations.violation_type_id', '=', 'violation_types.id')
            ->where('violation_types.category', $category)
            ->count();
    }
}
