@extends('layouts.admin')

@section('title', 'Tambah Pelanggaran Siswa')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tambah Data Pelanggaran Siswa</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.student-violations.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.student-violations.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="student_id" class="form-label">Siswa <span class="text-danger">*</span></label>
                                    <select class="form-select select2 @error('student_id') is-invalid @enderror" 
                                            id="student_id" 
                                            name="student_id" 
                                            required>
                                        <option value="">Pilih Siswa</option>
                                        @foreach($students as $student)
                                            <option value="{{ $student->id }}" 
                                                    data-nisn="{{ $student->nisn }}"
                                                    data-class="{{ $student->kelas }}"
                                                    {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                                {{ $student->name }} ({{ $student->nisn }}) - Kelas {{ $student->kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('student_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="violation_type_id" class="form-label">Jenis Pelanggaran <span class="text-danger">*</span></label>
                                    <select class="form-select @error('violation_type_id') is-invalid @enderror" 
                                            id="violation_type_id" 
                                            name="violation_type_id" 
                                            required>
                                        <option value="">Pilih Jenis Pelanggaran</option>
                                        @foreach($violationTypes->groupBy('category') as $category => $types)
                                            <optgroup label="{{ ucfirst($category) }}">
                                                @foreach($types as $type)
                                                    <option value="{{ $type->id }}" 
                                                            data-points="{{ $type->points }}"
                                                            data-category="{{ $type->category }}"
                                                            {{ old('violation_type_id') == $type->id ? 'selected' : '' }}>
                                                        {{ $type->name }} ({{ $type->points }} poin)
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    @error('violation_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="violation_date" class="form-label">Tanggal Pelanggaran <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('violation_date') is-invalid @enderror" 
                                           id="violation_date" 
                                           name="violation_date" 
                                           value="{{ old('violation_date', date('Y-m-d')) }}" 
                                           max="{{ date('Y-m-d') }}"
                                           required>
                                    @error('violation_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="violation_time" class="form-label">Waktu Pelanggaran</label>
                                    <input type="time" 
                                           class="form-control @error('violation_time') is-invalid @enderror" 
                                           id="violation_time" 
                                           name="violation_time" 
                                           value="{{ old('violation_time') }}">
                                    @error('violation_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Lokasi</label>
                                    <input type="text" 
                                           class="form-control @error('location') is-invalid @enderror" 
                                           id="location" 
                                           name="location" 
                                           value="{{ old('location') }}" 
                                           placeholder="Contoh: Ruang Kelas 10A">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reported_by" class="form-label">Dilaporkan Oleh</label>
                                    <input type="text" 
                                           class="form-control @error('reported_by') is-invalid @enderror" 
                                           id="reported_by" 
                                           name="reported_by" 
                                           value="{{ old('reported_by', auth()->user()->name) }}" 
                                           placeholder="Nama guru/staff yang melaporkan"
                                           list="teachers-list">
                                    <datalist id="teachers-list">
                                        @if(isset($teachers))
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->name }}">
                                            @endforeach
                                        @endif
                                    </datalist>
                                    @error('reported_by')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" 
                                            name="status" 
                                            required>
                                        <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>
                                            Pending
                                        </option>
                                        <option value="confirmed" {{ old('status') === 'confirmed' ? 'selected' : '' }}>
                                            Dikonfirmasi
                                        </option>
                                        <option value="resolved" {{ old('status') === 'resolved' ? 'selected' : '' }}>
                                            Diselesaikan
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi Pelanggaran</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="4"
                                              placeholder="Jelaskan detail pelanggaran yang terjadi...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Student Info Display -->
                        <div id="student-info" class="row" style="display: none;">
                            <div class="col-12">
                                <div class="alert alert-secondary">
                                    <h6><i class="fas fa-user"></i> Informasi Siswa</h6>
                                    <div id="student-details"></div>
                                </div>
                            </div>
                        </div>

                        <!-- High Violation Warning -->
                        <div id="violation-warning" class="row" style="display: none;">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle"></i> Peringatan</h6>
                                    <div id="warning-details"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Violation Info Display -->
                        <div id="violation-info" class="row" style="display: none;">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> Informasi Pelanggaran</h6>
                                    <div id="violation-details"></div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.student-violations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
.select2-container--bootstrap-5 .select2-selection {
    min-height: calc(2.25rem + 2px);
}
.violation-summary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 for student dropdown
    $('#student_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Cari siswa berdasarkan nama atau NISN...',
        allowClear: true,
        width: '100%'
    });

    // Initialize Select2 for violation type dropdown
    $('#violation_type_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Pilih jenis pelanggaran...',
        allowClear: true,
        width: '100%'
    });

    const studentSelect = document.getElementById('student_id');
    const violationTypeSelect = document.getElementById('violation_type_id');
    const studentInfo = document.getElementById('student-info');
    const studentDetails = document.getElementById('student-details');
    const violationInfo = document.getElementById('violation-info');
    const violationDetails = document.getElementById('violation-details');
    const violationWarning = document.getElementById('violation-warning');
    const warningDetails = document.getElementById('warning-details');
    
    // Handle student selection
    $('#student_id').on('select2:select', function(e) {
        const selectedOption = e.params.data.element;
        const studentId = selectedOption.value;
        
        if (studentId) {
            // Show loading state
            studentDetails.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2">Memuat informasi siswa...</span>
                </div>
            `;
            studentInfo.style.display = 'block';
            
            // Fetch student info via AJAX
            fetch(`/admin/students/${studentId}/info`)
                .then(response => response.json())
                .then(data => {
                    const student = data.student;
                    const totalPoints = data.total_points || 0;
                    const violationsCount = data.violations_count || 0;
                    const recentViolations = data.recent_violations || [];
                    
                    let pointsBadge = '';
                    if (totalPoints === 0) {
                        pointsBadge = '<span class="badge bg-success">Tidak ada pelanggaran</span>';
                    } else if (totalPoints <= 10) {
                        pointsBadge = `<span class="badge bg-warning">${totalPoints} poin</span>`;
                    } else {
                        pointsBadge = `<span class="badge bg-danger">${totalPoints} poin</span>`;
                    }
                    
                    let recentViolationsHtml = '';
                    if (recentViolations.length > 0) {
                        recentViolationsHtml = '<div class="mt-2"><small class="text-muted"><strong>Pelanggaran Terbaru:</strong></small><ul class="list-unstyled mt-1">';
                        recentViolations.forEach(violation => {
                            const date = new Date(violation.violation_date).toLocaleDateString('id-ID');
                            recentViolationsHtml += `<li><small class="text-muted">• ${violation.violation_type.name} (${date})</small></li>`;
                        });
                        recentViolationsHtml += '</ul></div>';
                    }
                    
                    studentDetails.innerHTML = `
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Nama:</strong><br>
                                <span class="text-muted">${student.name}</span>
                            </div>
                            <div class="col-md-2">
                                <strong>NISN:</strong><br>
                                <span class="text-muted">${student.nisn}</span>
                            </div>
                            <div class="col-md-2">
                                <strong>Kelas:</strong><br>
                                <span class="text-muted">${student.kelas}</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Total Pelanggaran:</strong><br>
                                <span class="text-muted">${violationsCount} kali</span>
                            </div>
                            <div class="col-md-2">
                                <strong>Total Poin:</strong><br>
                                ${pointsBadge}
                            </div>
                        </div>
                        ${recentViolationsHtml}
                    `;
                    
                    // Show warning if student has high violation points
                    currentStudentPoints = totalPoints; // Update global variable
                    if (totalPoints >= 15) {
                        warningDetails.innerHTML = `
                            Siswa ini sudah memiliki <strong>${totalPoints} poin pelanggaran</strong>. 
                            Pertimbangkan untuk memberikan tindakan khusus atau konseling sebelum menambah pelanggaran baru.
                        `;
                        violationWarning.style.display = 'block';
                    } else {
                        violationWarning.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error fetching student info:', error);
                    studentDetails.innerHTML = `
                        <div class="alert alert-warning mb-0">
                            <small><i class="fas fa-exclamation-triangle"></i> Gagal memuat informasi siswa</small>
                        </div>
                    `;
                });
        }
    });

    $('#student_id').on('select2:clear', function() {
        studentInfo.style.display = 'none';
        violationWarning.style.display = 'none';
        currentStudentPoints = 0; // Reset points when student is cleared
    });
    
    // Handle violation type selection
    $('#violation_type_id').on('select2:select', function(e) {
        const selectedOption = e.params.data.element;
        
        if (selectedOption) {
            const points = selectedOption.dataset.points;
            const category = selectedOption.dataset.category;
            const name = selectedOption.text.split(' (')[0];
            
            let categoryBadge = '';
            let categoryIcon = '';
            switch(category) {
                case 'ringan':
                    categoryBadge = '<span class="badge bg-success">Ringan</span>';
                    categoryIcon = 'fas fa-check-circle text-success';
                    break;
                case 'sedang':
                    categoryBadge = '<span class="badge bg-warning">Sedang</span>';
                    categoryIcon = 'fas fa-exclamation-triangle text-warning';
                    break;
                case 'berat':
                    categoryBadge = '<span class="badge bg-danger">Berat</span>';
                    categoryIcon = 'fas fa-times-circle text-danger';
                    break;
            }
            
            violationDetails.innerHTML = `
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-2">
                            <i class="${categoryIcon} me-2"></i>
                            <strong>${name}</strong>
                        </div>
                        <div>
                            <strong>Kategori:</strong> ${categoryBadge}
                            <strong class="ms-3">Poin:</strong> <span class="badge bg-info">${points} poin</span>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="violation-summary text-center">
                            <div class="h4 mb-0">${points}</div>
                            <small>Poin Pelanggaran</small>
                        </div>
                    </div>
                </div>
            `;
            violationInfo.style.display = 'block';
        }
    });

    $('#violation_type_id').on('select2:clear', function() {
        violationInfo.style.display = 'none';
    });
    
    // Form validation enhancement
    const form = document.querySelector('form');
    let currentStudentPoints = 0;
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        const requiredFields = ['student_id', 'violation_type_id', 'violation_date', 'status'];
        
        requiredFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
                
                // Show custom error message
                let errorDiv = field.parentNode.querySelector('.invalid-feedback');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    field.parentNode.appendChild(errorDiv);
                }
                errorDiv.textContent = 'Field ini wajib diisi.';
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            // Scroll to first error
            const firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
            return;
        }
        
        // Check if student has high violation points and show confirmation
        if (currentStudentPoints >= 15) {
            e.preventDefault();
            
            if (!confirm(`Siswa ini sudah memiliki ${currentStudentPoints} poin pelanggaran. Apakah Anda yakin ingin menambah pelanggaran baru?\n\nPertimbangkan untuk memberikan konseling atau tindakan khusus terlebih dahulu.`)) {
                return;
            }
            
            // If confirmed, submit the form
            form.removeEventListener('submit', arguments.callee);
            form.submit();
        }
    });
    
    // Auto-set current time when page loads
    const timeInput = document.getElementById('violation_time');
    if (!timeInput.value) {
        const now = new Date();
        const currentTime = now.getHours().toString().padStart(2, '0') + ':' + 
                           now.getMinutes().toString().padStart(2, '0');
        timeInput.value = currentTime;
    }
    
    // Trigger events if there are already selected values (for edit mode or validation errors)
    if (studentSelect.value) {
        $('#student_id').trigger('select2:select', {
            params: {
                data: {
                    element: studentSelect.options[studentSelect.selectedIndex]
                }
            }
        });
    }
    
    if (violationTypeSelect.value) {
        $('#violation_type_id').trigger('select2:select', {
            params: {
                data: {
                    element: violationTypeSelect.options[violationTypeSelect.selectedIndex]
                }
            }
        });
    }
});
</script>
@endpush