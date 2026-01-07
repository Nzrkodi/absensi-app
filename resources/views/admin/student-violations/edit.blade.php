@extends('layouts.admin')

@section('title', 'Edit Pelanggaran Siswa')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Data Pelanggaran Siswa</h3>
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

                    <form action="{{ route('admin.student-violations.update', $studentViolation) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="student_id" class="form-label">Siswa <span class="text-danger">*</span></label>
                                    <select class="form-select @error('student_id') is-invalid @enderror" 
                                            id="student_id" 
                                            name="student_id" 
                                            required>
                                        <option value="">Pilih Siswa</option>
                                        @foreach($students as $student)
                                            <option value="{{ $student->id }}" 
                                                    {{ old('student_id', $studentViolation->student_id) == $student->id ? 'selected' : '' }}>
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
                                                            {{ old('violation_type_id', $studentViolation->violation_type_id) == $type->id ? 'selected' : '' }}>
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
                                           value="{{ old('violation_date', $studentViolation->violation_date->format('Y-m-d')) }}" 
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
                                           value="{{ old('violation_time', $studentViolation->violation_time ? $studentViolation->violation_time->format('H:i') : '') }}">
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
                                           value="{{ old('location', $studentViolation->location) }}" 
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
                                           value="{{ old('reported_by', $studentViolation->reported_by) }}" 
                                           placeholder="Nama guru/staff yang melaporkan">
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
                                        <option value="pending" {{ old('status', $studentViolation->status) === 'pending' ? 'selected' : '' }}>
                                            Pending
                                        </option>
                                        <option value="confirmed" {{ old('status', $studentViolation->status) === 'confirmed' ? 'selected' : '' }}>
                                            Dikonfirmasi
                                        </option>
                                        <option value="resolved" {{ old('status', $studentViolation->status) === 'resolved' ? 'selected' : '' }}>
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
                                              placeholder="Jelaskan detail pelanggaran yang terjadi...">{{ old('description', $studentViolation->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="resolution_notes" class="form-label">Catatan Penyelesaian</label>
                                    <textarea class="form-control @error('resolution_notes') is-invalid @enderror" 
                                              id="resolution_notes" 
                                              name="resolution_notes" 
                                              rows="3"
                                              placeholder="Catatan tindakan yang diambil atau penyelesaian masalah...">{{ old('resolution_notes', $studentViolation->resolution_notes) }}</textarea>
                                    @error('resolution_notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Violation Info Display -->
                        <div id="violation-info" class="row">
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
                                <i class="fas fa-save"></i> Perbarui
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const violationTypeSelect = document.getElementById('violation_type_id');
    const violationInfo = document.getElementById('violation-info');
    const violationDetails = document.getElementById('violation-details');
    
    function updateViolationInfo() {
        const selectedOption = violationTypeSelect.options[violationTypeSelect.selectedIndex];
        
        if (selectedOption.value) {
            const points = selectedOption.dataset.points;
            const category = selectedOption.dataset.category;
            const name = selectedOption.text.split(' (')[0];
            
            let categoryBadge = '';
            switch(category) {
                case 'ringan':
                    categoryBadge = '<span class="badge bg-success">Ringan</span>';
                    break;
                case 'sedang':
                    categoryBadge = '<span class="badge bg-warning">Sedang</span>';
                    break;
                case 'berat':
                    categoryBadge = '<span class="badge bg-danger">Berat</span>';
                    break;
            }
            
            violationDetails.innerHTML = `
                <strong>Jenis:</strong> ${name}<br>
                <strong>Kategori:</strong> ${categoryBadge}<br>
                <strong>Poin:</strong> <span class="badge bg-info">${points} poin</span>
            `;
            violationInfo.style.display = 'block';
        } else {
            violationInfo.style.display = 'none';
        }
    }
    
    violationTypeSelect.addEventListener('change', updateViolationInfo);
    
    // Initialize on page load
    updateViolationInfo();
});
</script>
@endpush