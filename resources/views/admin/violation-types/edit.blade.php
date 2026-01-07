@extends('layouts.admin')

@section('title', 'Edit Jenis Pelanggaran')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Jenis Pelanggaran: {{ $violationType->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.violation-types.index') }}" class="btn btn-secondary">
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

                    <form action="{{ route('admin.violation-types.update', $violationType) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Pelanggaran <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $violationType->name) }}" 
                                           placeholder="Contoh: Terlambat Masuk Kelas"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                                    <select class="form-select @error('category') is-invalid @enderror" 
                                            id="category" 
                                            name="category" 
                                            required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="ringan" {{ old('category', $violationType->category) === 'ringan' ? 'selected' : '' }}>
                                            Ringan (1-5 poin)
                                        </option>
                                        <option value="sedang" {{ old('category', $violationType->category) === 'sedang' ? 'selected' : '' }}>
                                            Sedang (6-15 poin)
                                        </option>
                                        <option value="berat" {{ old('category', $violationType->category) === 'berat' ? 'selected' : '' }}>
                                            Berat (16+ poin)
                                        </option>
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="points" class="form-label">Poin Pelanggaran <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('points') is-invalid @enderror" 
                                           id="points" 
                                           name="points" 
                                           value="{{ old('points', $violationType->points) }}" 
                                           min="1" 
                                           max="100"
                                           placeholder="1-100"
                                           required>
                                    @error('points')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-9">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="3"
                                              placeholder="Jelaskan detail pelanggaran ini...">{{ old('description', $violationType->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" 
                                            name="status" 
                                            required>
                                        <option value="active" {{ old('status', $violationType->status) === 'active' ? 'selected' : '' }}>
                                            Aktif
                                        </option>
                                        <option value="inactive" {{ old('status', $violationType->status) === 'inactive' ? 'selected' : '' }}>
                                            Nonaktif
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        @if($violationType->studentViolations()->count() > 0)
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Perhatian:</strong> Jenis pelanggaran ini sudah digunakan pada {{ $violationType->studentViolations()->count() }} data pelanggaran siswa. 
                                        Perubahan akan mempengaruhi data yang sudah ada.
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Panduan Kategori:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li><strong>Ringan (1-5 poin):</strong> Pelanggaran kecil seperti terlambat, tidak mengerjakan PR</li>
                                        <li><strong>Sedang (6-15 poin):</strong> Pelanggaran menengah seperti bolos, tidak sopan</li>
                                        <li><strong>Berat (16+ poin):</strong> Pelanggaran serius seperti berkelahi, merokok</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.violation-types.index') }}" class="btn btn-secondary">
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
    const categorySelect = document.getElementById('category');
    const pointsInput = document.getElementById('points');
    
    categorySelect.addEventListener('change', function() {
        const category = this.value;
        
        switch(category) {
            case 'ringan':
                pointsInput.setAttribute('max', '5');
                break;
            case 'sedang':
                pointsInput.setAttribute('max', '15');
                break;
            case 'berat':
                pointsInput.setAttribute('max', '100');
                break;
            default:
                pointsInput.setAttribute('max', '100');
        }
    });
    
    // Set initial max value based on current category
    categorySelect.dispatchEvent(new Event('change'));
});
</script>
@endpush