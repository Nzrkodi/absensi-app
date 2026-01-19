@extends('layouts.admin')

@section('title', 'Tambah Lokasi Sekolah')
@section('header', 'Tambah Lokasi Sekolah')

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <h6><i class="fas fa-exclamation-triangle me-2"></i>Ada kesalahan dalam form:</h6>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Form Tambah Lokasi</h5>
                    <a href="{{ route('admin.school-locations.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.school-locations.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" 
                                       placeholder="Contoh: Gedung Utama, Lapangan, Lab Komputer">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="color" class="form-label">Warna Marker</label>
                                <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror" 
                                       id="color" name="color" value="{{ old('color', '#007bff') }}">
                                @error('color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="latitude" class="form-label">Latitude <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('latitude') is-invalid @enderror" 
                                       id="latitude" name="latitude" value="{{ old('latitude') }}" 
                                       placeholder="-5.147665" pattern="^-?[0-9]*\.?[0-9]+$">
                                @error('latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="longitude" class="form-label">Longitude <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('longitude') is-invalid @enderror" 
                                       id="longitude" name="longitude" value="{{ old('longitude') }}" 
                                       placeholder="119.432732" pattern="^-?[0-9]*\.?[0-9]+$">
                                @error('longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="radius_meters" class="form-label">Radius (meter) <span class="text-danger">*</span></label>
                                <input type="number" min="10" max="1000" class="form-control @error('radius_meters') is-invalid @enderror" 
                                       id="radius_meters" name="radius_meters" value="{{ old('radius_meters', 100) }}">
                                <div class="form-text">Minimum 10 meter, maksimum 1000 meter</div>
                                @error('radius_meters')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Aktif (digunakan untuk validasi absensi)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Deskripsi lokasi (opsional)">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Simpan Lokasi
                        </button>
                        <button type="button" class="btn btn-success" onclick="getCurrentLocation()">
                            <i class="fas fa-map-marker-alt me-1"></i>Gunakan Lokasi Saat Ini
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle text-info me-2"></i>Tips Menentukan Lokasi
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <h6>Cara Mendapatkan Koordinat:</h6>
                    <ol>
                        <li>Buka Google Maps di HP/komputer</li>
                        <li>Cari lokasi sekolah Anda</li>
                        <li>Klik dan tahan pada titik yang diinginkan</li>
                        <li>Koordinat akan muncul di bagian bawah</li>
                        <li>Salin dan paste ke form ini</li>
                    </ol>
                    
                    <hr>
                    
                    <h6>Rekomendasi Radius:</h6>
                    <ul class="mb-0">
                        <li><strong>Gedung kecil:</strong> 30-50 meter</li>
                        <li><strong>Gedung utama:</strong> 50-100 meter</li>
                        <li><strong>Area lapangan:</strong> 100-200 meter</li>
                        <li><strong>Kompleks sekolah:</strong> 200-500 meter</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-warning bg-opacity-10">
                <h6 class="mb-0 text-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>Perhatian
                </h6>
            </div>
            <div class="card-body">
                <div class="small text-muted">
                    <ul class="mb-0">
                        <li>Pastikan koordinat akurat untuk validasi yang tepat</li>
                        <li>Radius terlalu kecil akan menyulitkan siswa</li>
                        <li>Radius terlalu besar akan mengurangi akurasi</li>
                        <li>Test lokasi setelah menyimpan</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function getCurrentLocation() {
    if (navigator.geolocation) {
        // Show loading state
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Mengambil lokasi...';
        button.disabled = true;
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                // Set values with high precision
                document.getElementById('latitude').value = position.coords.latitude.toFixed(8);
                document.getElementById('longitude').value = position.coords.longitude.toFixed(8);
                
                // Show success message
                showToast('success', `Lokasi berhasil diambil!\nLatitude: ${position.coords.latitude.toFixed(8)}\nLongitude: ${position.coords.longitude.toFixed(8)}`);
                
                // Restore button
                button.innerHTML = originalText;
                button.disabled = false;
            },
            function(error) {
                let message = 'Tidak dapat mengambil lokasi: ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        message += 'Izin lokasi ditolak. Silakan izinkan akses lokasi di browser.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        message += 'Lokasi tidak tersedia. Pastikan GPS aktif.';
                        break;
                    case error.TIMEOUT:
                        message += 'Timeout. Coba lagi dalam beberapa saat.';
                        break;
                    default:
                        message += 'Error tidak diketahui.';
                        break;
                }
                showToast('error', message);
                
                // Restore button
                button.innerHTML = originalText;
                button.disabled = false;
            },
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            }
        );
    } else {
        showToast('error', 'Browser tidak mendukung geolocation. Gunakan browser yang lebih baru.');
    }
}

function showToast(type, message) {
    // Create better toast notification
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" 
             role="alert" aria-live="assertive" aria-atomic="true" id="${toastId}">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                    ${message.replace(/\n/g, '<br>')}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                        data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    // Add to toast container (create if doesn't exist)
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Show toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: type === 'success' ? 3000 : 5000
    });
    toast.show();
    
    // Remove from DOM after hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}
</script>
@endpush
@endsection