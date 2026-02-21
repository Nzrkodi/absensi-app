<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'email',
        'jenis_kelamin',
        'nomor_hp',
        'jabatan',
        'mata_pelajaran',
        'status',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class);
    }

    public function getJenisKelaminLabelAttribute()
    {
        return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    }

    public function getStatusBadgeAttribute()
    {
        return $this->status === 'active' 
            ? '<span class="badge bg-success">Aktif</span>' 
            : '<span class="badge bg-secondary">Tidak Aktif</span>';
    }
}
