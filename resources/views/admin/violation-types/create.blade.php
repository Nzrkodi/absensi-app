@extends('layouts.admin')

@section('title', 'Tambah Jenis Pelanggaran')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tambah Jenis Pelanggaran Baru</h3>
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

                    <form action="{{ route('admin.violation-types.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Pelanggaran <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
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
                                        <option value="ringan" {{ old('category') === 'ringan' ? 'selected' : '' }}>
                                            Ringan (1-5 poin)
                                        </option>
                                        <option value="sedang" {{ old('category') === 'sedang' ? 'selected' : '' }}>
                                            Sedang (6-15 poin)
                                        </option>
                                        <option value="berat" {{ old('category') === 'berat' ? 'selected' : '' }}>
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
                                           value="{{ old('points') }}" 
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
                                              placeholder="Jelaskan detail pelanggaran ini...">{{ old('description') }}</textarea>
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
                                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                                            Aktif
                                        </option>
                                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>
                                            Nonaktif
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category');
    const pointsInput = document.getElementById('points');
    
    categorySelect.addEventListener('change', function() {
        const category = this.value;
        let suggestedPoints = '';
        
        switch(category) {
            case 'ringan':
                suggestedPoints = '3';
                pointsInput.setAttribute('max', '5');
                break;
            case 'sedang':
                suggestedPoints = '10';
                pointsInput.setAttribute('max', '15');
                break;
            case 'berat':
                suggestedPoints = '20';
                pointsInput.setAttribute('max', '100');
                break;
            default:
                pointsInput.setAttribute('max', '100');
        }
        
        if (suggestedPoints && !pointsInput.value) {
            pointsInput.value = suggestedPoints;
        }
    });
});
</script>
@endpush