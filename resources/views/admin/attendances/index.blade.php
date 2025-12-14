@extends('layouts.admin')

@section('title', 'Attendance')
@section('header', 'Data Attendance')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="card-title mb-0">Daftar Absensi</h5>
        <a href="{{ route('admin.attendances.create') }}" class="btn btn-primary">
            + Tambah Absensi
        </a>
    </div>
    
    <!-- Filter -->
    <div class="card-body bg-light border-bottom">
        <form action="{{ route('admin.attendances.index') }}" method="GET" class="row g-3">
            <div class="col-auto">
                <input type="date" name="date" value="{{ request('date') }}" class="form-control">
            </div>
            <div class="col-auto">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Hadir</option>
                    <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                    <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Terlambat</option>
                    <option value="sick" {{ request('status') === 'sick' ? 'selected' : '' }}>Sakit</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-secondary">Filter</button>
            </div>
        </form>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances ?? [] as $index => $attendance)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $attendance->student->name ?? '-' }}</td>
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
                        <td>{{ $attendance->notes ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.attendances.edit', $attendance) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('admin.attendances.destroy', $attendance) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada data absensi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances instanceof \Illuminate\Pagination\LengthAwarePaginator && $attendances->hasPages())
        <div class="mt-3">
            {{ $attendances->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
