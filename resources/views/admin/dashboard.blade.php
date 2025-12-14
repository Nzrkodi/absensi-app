@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="row g-4 mb-4">
    <!-- Card Total Siswa -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-primary bg-opacity-10 rounded">
                        <svg class="text-primary" width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="ms-3">
                        <p class="text-muted small mb-0">Total Siswa</p>
                        <p class="h4 fw-bold text-dark mb-0">{{ $totalStudents ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Hadir Hari Ini -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-success bg-opacity-10 rounded">
                        <svg class="text-success" width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ms-3">
                        <p class="text-muted small mb-0">Hadir Hari Ini</p>
                        <p class="h4 fw-bold text-dark mb-0">{{ $presentToday ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Tidak Hadir -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="p-3 bg-danger bg-opacity-10 rounded">
                        <svg class="text-danger" width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ms-3">
                        <p class="text-muted small mb-0">Tidak Hadir</p>
                        <p class="h4 fw-bold text-dark mb-0">{{ $absentToday ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Attendance -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h5 class="card-title mb-0">Absensi Terbaru</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nama Siswa</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAttendances ?? [] as $attendance)
                    <tr>
                        <td>{{ $attendance->student->name }}</td>
                        <td>{{ $attendance->date->format('d M Y') }}</td>
                        <td>
                            @switch($attendance->status)
                                @case('present')
                                    <span class="badge bg-success">Present</span>
                                    @break
                                @case('absent')
                                    <span class="badge bg-danger">Absent</span>
                                    @break
                                @case('late')
                                    <span class="badge bg-warning text-dark">Late</span>
                                    @break
                                @case('sick')
                                    <span class="badge bg-info">Sick</span>
                                    @break
                            @endswitch
                        </td>
                        <td>{{ $attendance->created_at->format('H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Belum ada data absensi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
