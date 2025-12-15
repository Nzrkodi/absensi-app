@extends('layouts.admin')

@section('title', 'Pengaturan')
@section('header', 'Pengaturan Sistem')

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

<form action="{{ route('admin.settings.update') }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <!-- Pengaturan Absensi -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clock text-primary me-2"></i>
                        Pengaturan Absensi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="school_start_time" class="form-label">
                                Waktu Mulai Sekolah <span class="text-danger">*</span>
                            </label>
                            <input type="time" 
                                   class="form-control @error('school_start_time') is-invalid @enderror" 
                                   id="school_start_time" 
                                   name="school_start_time" 
                                   value="{{ old('school_start_time', $settings['school_start_time']) }}" 
                                   required>
                            <small class="text-muted">Jam berapa siswa harus sudah masuk sekolah</small>
                            @error('school_start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="late_tolerance_minutes" class="form-label">
                                Toleransi Keterlambatan <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control @error('late_tolerance_minutes') is-invalid @enderror" 
                                       id="late_tolerance_minutes" 
                                       name="late_tolerance_minutes" 
                                       value="{{ old('late_tolerance_minutes', $settings['late_tolerance_minutes']) }}" 
                                       min="0" 
                                       max="120" 
                                       required>
                                <span class="input-group-text">menit</span>
                                @error('late_tolerance_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Setelah waktu ini, siswa dianggap terlambat</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="auto_absent_time" class="form-label">
                                Waktu Auto Absent <span class="text-danger">*</span>
                            </label>
                            <input type="time" 
                                   class="form-control @error('auto_absent_time') is-invalid @enderror" 
                                   id="auto_absent_time" 
                                   name="auto_absent_time" 
                                   value="{{ old('auto_absent_time', $settings['auto_absent_time']) }}" 
                                   required>
                            <small class="text-muted">Jam berapa sistem otomatis menandai siswa absent</small>
                            @error('auto_absent_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="school_name" class="form-label">
                                Nama Sekolah <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('school_name') is-invalid @enderror" 
                                   id="school_name" 
                                   name="school_name" 
                                   value="{{ old('school_name', \App\Models\Setting::get('school_name', 'SMA Negeri 1')) }}" 
                                   required>
                            @error('school_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="allow_early_clockin" 
                                       name="allow_early_clockin" 
                                       {{ \App\Models\Setting::get('allow_early_clockin', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="allow_early_clockin">
                                    Izinkan Clock In Sebelum Jam Mulai Sekolah
                                </label>
                                <small class="text-muted d-block">Jika dicentang, siswa bisa clock in sebelum jam mulai sekolah</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Info Panel -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        Informasi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Contoh Skenario:</strong>
                        <ul class="list-unstyled mt-2 small">
                            <li>• Mulai sekolah: <span class="text-primary" id="preview-start">{{ $settings['school_start_time'] }}</span></li>
                            <li>• Batas terlambat: <span class="text-warning" id="preview-late"></span></li>
                            <li>• Auto absent: <span class="text-danger" id="preview-absent">{{ $settings['auto_absent_time'] }}</span></li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-info small">
                        <i class="fas fa-lightbulb me-1"></i>
                        <strong>Tips:</strong> Sesuaikan waktu dengan jadwal sekolah Anda. Sistem akan otomatis menandai siswa terlambat atau absent berdasarkan pengaturan ini.
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan Pengaturan
                        </button>
                        
                        <button type="button" class="btn btn-outline-secondary" onclick="resetToDefault()">
                            <i class="fas fa-undo me-2"></i>Reset ke Default
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Reset Form (Hidden) -->
<form id="resetForm" action="{{ route('admin.settings.reset') }}" method="POST" style="display: none;">
    @csrf
    @method('POST')
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update preview when values change
    function updatePreview() {
        const startTime = document.getElementById('school_start_time').value;
        const toleranceMinutes = parseInt(document.getElementById('late_tolerance_minutes').value) || 0;
        const absentTime = document.getElementById('auto_absent_time').value;
        
        document.getElementById('preview-start').textContent = startTime;
        document.getElementById('preview-absent').textContent = absentTime;
        
        if (startTime && toleranceMinutes) {
            const [hours, minutes] = startTime.split(':').map(Number);
            const lateTime = new Date();
            lateTime.setHours(hours, minutes + toleranceMinutes, 0, 0);
            
            const lateTimeStr = lateTime.toTimeString().slice(0, 5);
            document.getElementById('preview-late').textContent = lateTimeStr;
        }
    }
    
    // Add event listeners
    document.getElementById('school_start_time').addEventListener('change', updatePreview);
    document.getElementById('late_tolerance_minutes').addEventListener('input', updatePreview);
    document.getElementById('auto_absent_time').addEventListener('change', updatePreview);
    
    // Initial preview update
    updatePreview();
});

function resetToDefault() {
    if (confirm('Apakah Anda yakin ingin mereset semua pengaturan ke nilai default?')) {
        document.getElementById('resetForm').submit();
    }
}
</script>
@endpush
@endsection