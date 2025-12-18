@extends('layouts.admin')

@section('title', 'Laporan Absensi')
@section('header', 'Laporan Absensi')

@section('content')
<!-- Filter Section -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="card-title mb-0">Filter Laporan</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.reports.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                </div>
                <div class="col-md-3">
                    <label for="class_id" class="form-label">Kelas</label>
                    <select class="form-select" id="class_id" name="class_id">
                        <option value="">Semua Kelas</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="student_id" class="form-label">Siswa</label>
                    <select class="form-select" id="student_id" name="student_id">
                        <option value="">Semua Siswa</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ $studentId == $student->id ? 'selected' : '' }}>
                                {{ $student->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-danger">
                            <i class="fas fa-refresh me-1"></i> Reset
                        </a>
                        <a href="{{ route('admin.reports.preview', request()->query()) }}" class="btn btn-success">
                            <i class="fas fa-eye me-1"></i> Preview & Export
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row g-4 mb-4">
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="h4 fw-bold text-primary">{{ $summary->total ?? 0 }}</div>
                <div class="small text-muted">Total Record</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="h4 fw-bold text-success">{{ $summary->present ?? 0 }}</div>
                <div class="small text-muted">Hadir</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="h4 fw-bold text-warning">{{ $summary->late ?? 0 }}</div>
                <div class="small text-muted">Terlambat</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="h4 fw-bold text-danger">{{ $summary->absent ?? 0 }}</div>
                <div class="small text-muted">Tidak Hadir</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="h4 fw-bold text-info">{{ $summary->sick ?? 0 }}</div>
                <div class="small text-muted">Sakit</div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <div class="h4 fw-bold text-secondary">{{ $summary->permission ?? 0 }}</div>
                <div class="small text-muted">Izin</div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Data -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0">
            Data Absensi 
            <span class="text-muted">
                ({{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }})
            </span>
        </h6>
        <div class="text-muted small">
            Total: {{ $attendances->total() }} record
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->date->format('d M Y') }}</td>
                        <td>{{ $attendance->student->nisn ?? '-' }}</td>
                        <td>{{ $attendance->student->name }}</td>
                        <td>{{ $attendance->student->class->name ?? '-' }}</td>
                        <td>
                            @if($attendance->clock_in)
                                <span class="text-success">{{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($attendance->clock_out)
                                <span class="text-info">{{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{!! $attendance->status_badge !!}</td>
                        <td>
                            @if($attendance->notes)
                                <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $attendance->notes }}">
                                    {{ $attendance->notes }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.reports.student', ['student' => $attendance->student->id, 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
                               class="btn btn-outline-primary btn-sm" title="Lihat Detail Siswa">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            Tidak ada data absensi untuk periode yang dipilih
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($attendances->hasPages())
        <div class="p-3">
            {{ $attendances->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-update students when class changes
    document.getElementById('class_id').addEventListener('change', function() {
        const classId = this.value;
        const studentSelect = document.getElementById('student_id');
        
        // Clear current options except "Semua Siswa"
        studentSelect.innerHTML = '<option value="">Semua Siswa</option>';
        
        if (classId) {
            // Fetch students for selected class
            fetch(`/admin/students?class_id=${classId}&ajax=1`)
                .then(response => response.json())
                .then(data => {
                    data.students.forEach(student => {
                        const option = document.createElement('option');
                        option.value = student.id;
                        option.textContent = student.name;
                        studentSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching students:', error);
                });
        }
    });
});
</script>
@endpush
@endsection