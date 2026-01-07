<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViolationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'points',
        'status'
    ];

    /**
     * Relasi ke student violations
     */
    public function studentViolations()
    {
        return $this->hasMany(StudentViolation::class);
    }

    /**
     * Scope untuk violation types yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get violation types by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get badge color based on category
     */
    public function getBadgeColorAttribute()
    {
        return match($this->category) {
            'ringan' => 'success',
            'sedang' => 'warning',
            'berat' => 'danger',
            default => 'secondary'
        };
    }
}