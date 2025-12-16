@extends('layouts.admin')

@section('title', 'Hari Libur')
@section('header', 'Manajemen Hari Libur')

@section('content')
<!-- Alert Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Holiday Status Today -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-calendar-day fa-2x text-primary"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">Status Hari Ini</h6>
                        <div id="todayStatus">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                            Mengecek status...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 py-3">
        <h5 class="card-title mb-0">Daftar Hari Libur - Tahun {{ $year }}</h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
                <i class="fas fa-plus"></i> Tambah Libur
            </button>
            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#weekendModal">
                <i class="fas fa-calendar-week"></i> Buat Weekend
            </button>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card-body bg-light border-bottom">
        <form action="{{ route('admin.holidays.index') }}" method="GET">
            <div class="row g-2">
                <div class="col-12 col-md-3">
                    <select name="year" class="form-select">
                        @foreach($years as $yearOption)
                            <option value="{{ $yearOption }}" {{ $year == $yearOption ? 'selected' : '' }}>
                                {{ $yearOption }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <select name="type" class="form-select">
                        <option value="">Semua Jenis</option>
                        <option value="national" {{ request('type') == 'national' ? 'selected' : '' }}>Nasional</option>
                        <option value="school" {{ request('type') == 'school' ? 'selected' : '' }}>Sekolah</option>
                        <option value="weekend" {{ request('type') == 'weekend' ? 'selected' : '' }}>Weekend</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama hari libur..." class="form-control">
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Cari</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body p-0 p-md-3">
        <!-- Desktop Table -->
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Jenis</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($holidays ?? [] as $index => $holiday)
                    <tr>
                        <td>{{ $holidays->firstItem() + $index }}</td>
                        <td>{{ $holiday->date->format('d M Y') }}</td>
                        <td>{{ $holiday->name }}</td>
                        <td>{!! $holiday->type_badge !!}</td>
                        <td>{{ Str::limit($holiday->description ?? '-', 50) }}</td>
                        <td>
                            @if($holiday->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-outline-primary btn-sm btn-edit" 
                                        data-holiday-id="{{ $holiday->id }}"
                                        data-name="{{ $holiday->name }}"
                                        data-date="{{ $holiday->date->format('Y-m-d') }}"
                                        data-type="{{ $holiday->type }}"
                                        data-description="{{ $holiday->description }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <form action="{{ route('admin.holidays.toggle', $holiday) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-{{ $holiday->is_active ? 'warning' : 'success' }} btn-sm" 
                                            title="{{ $holiday->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="fas fa-{{ $holiday->is_active ? 'eye-slash' : 'eye' }}"></i>
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.holidays.destroy', $holiday) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm btn-delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada data hari libur</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="d-md-none">
            @forelse($holidays ?? [] as $holiday)
            <div class="border-bottom p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="mb-1">{{ $holiday->name }}</h6>
                        <small class="text-muted">{{ $holiday->date->format('d M Y') }}</small>
                    </div>
                    <div class="d-flex gap-1">
                        {!! $holiday->type_badge !!}
                        @if($holiday->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-secondary">Nonaktif</span>
                        @endif
                    </div>
                </div>
                
                @if($holiday->description)
                <p class="small text-muted mb-2">{{ $holiday->description }}</p>
                @endif
                
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm btn-edit" 
                            data-holiday-id="{{ $holiday->id }}"
                            data-name="{{ $holiday->name }}"
                            data-date="{{ $holiday->date->format('Y-m-d') }}"
                            data-type="{{ $holiday->type }}"
                            data-description="{{ $holiday->description }}">
                        Edit
                    </button>
                    
                    <form action="{{ route('admin.holidays.toggle', $holiday) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-{{ $holiday->is_active ? 'warning' : 'success' }} btn-sm">
                            {{ $holiday->is_active ? 'Nonaktif' : 'Aktif' }}
                        </button>
                    </form>
                    
                    <form action="{{ route('admin.holidays.destroy', $holiday) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm btn-delete">Hapus</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4">Belum ada data hari libur</div>
            @endforelse
        </div>

        @if($holidays instanceof \Illuminate\Pagination\LengthAwarePaginator && $holidays->hasPages())
        <div class="p-3">
            {{ $holidays->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add Holiday Modal -->
<div class="modal fade" id="addHolidayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Hari Libur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.holidays.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Hari Libur <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Jenis <span class="text-danger">*</span></label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Pilih Jenis</option>
                            <option value="national">Nasional</option>
                            <option value="school">Sekolah</option>
                            <option value="weekend">Weekend</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Holiday Modal -->
<div class="modal fade" id="editHolidayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Hari Libur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editHolidayForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Nama Hari Libur <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="edit_date" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_type" class="form-label">Jenis <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_type" name="type" required>
                            <option value="">Pilih Jenis</option>
                            <option value="national">Nasional</option>
                            <option value="school">Sekolah</option>
                            <option value="weekend">Weekend</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Weekend Modal -->
<div class="modal fade" id="weekendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Hari Libur Weekend</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.holidays.weekends') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="weekend_year" class="form-label">Tahun <span class="text-danger">*</span></label>
                        <select class="form-select" id="weekend_year" name="year" required>
                            @for($y = 2024; $y <= 2030; $y++)
                                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Fitur ini akan otomatis membuat hari libur untuk semua Sabtu dan Minggu dalam tahun yang dipilih.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">Buat Weekend</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check today's holiday status
    checkTodayStatus();
    
    // Handle edit buttons
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function() {
            const holidayId = this.getAttribute('data-holiday-id');
            const name = this.getAttribute('data-name');
            const date = this.getAttribute('data-date');
            const type = this.getAttribute('data-type');
            const description = this.getAttribute('data-description');
            
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_date').value = date;
            document.getElementById('edit_type').value = type;
            document.getElementById('edit_description').value = description || '';
            
            document.getElementById('editHolidayForm').action = `/admin/holidays/${holidayId}`;
            
            const modal = new bootstrap.Modal(document.getElementById('editHolidayModal'));
            modal.show();
        });
    });
});

function checkTodayStatus() {
    fetch('/admin/holidays/check-today')
        .then(response => response.json())
        .then(data => {
            const statusDiv = document.getElementById('todayStatus');
            
            if (data.is_holiday) {
                statusDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-times text-danger me-2"></i>
                        <div>
                            <strong class="text-danger">Hari Libur</strong>
                            <div class="small text-muted">${data.holiday.name} (${data.holiday.type})</div>
                        </div>
                    </div>
                `;
            } else {
                statusDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-check text-success me-2"></i>
                        <strong class="text-success">Hari Sekolah</strong>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error checking today status:', error);
            document.getElementById('todayStatus').innerHTML = `
                <span class="text-muted">Gagal mengecek status hari ini</span>
            `;
        });
}
</script>
@endpush
@endsection