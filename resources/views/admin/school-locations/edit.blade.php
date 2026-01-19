@extends('layouts.admin')

@section('title', 'Edit Lokasi Sekolah')
@section('header', 'Edit Lokasi Sekolah')

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
                    <h5 class="mb-0">Edit: {{ $schoolLocation->name }}</h5>
                    <a href="{{ route('admin.school-locations.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.school-locations.update', $schoolLocation) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $schoolLocation->name) }}" 
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
                                       id="color" name="color" value="{{ old('color', $schoolLocation->color) }}">
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
                                       id="latitude" name="latitude" value="{{ old('latitude', $schoolLocation->latitude) }}" 
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
                                       id="longitude" name="longitude" value="{{ old('longitude', $schoolLocation->longitude) }}" 
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
                                       id="radius_meters" name="radius_meters" value="{{ old('radius_meters', $schoolLocation->radius_meters) }}">
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
                                           {{ old('is_active', $schoolLocation->is_active) ? 'checked' : '' }}>
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
                                  placeholder="Deskripsi lokasi (opsional)">{{ old('description', $schoolLocation->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Lokasi
                        </button>
                        <button type="button" class="btn btn-success" onclick="getCurrentLocation()">
                            <i class="fas fa-map-marker-alt me-1"></i>Gunakan Lokasi Saat Ini
                        </button>
                        <a href="https://maps.google.com/?q={{ $schoolLocation->latitude }},{{ $schoolLocation->longitude }}" 
                           target="_blank" class="btn btn-info">
                            <i class="fas fa-external-link-alt me-1"></i>Lihat di Maps
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-map-marker-alt text-primary me-2"></i>Informasi Lokasi
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <div class="row mb-2">
                        <div class="col-4"><strong>Nama:</strong></div>
                        <div class="col-8">{{ $schoolLocation->name }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Koordinat:</strong></div>
                        <div class="col-8 font-monospace">{{ $schoolLocation->latitude }}, {{ $schoolLocation->longitude }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Radius:</strong></div>
                        <div class="col-8">{{ $schoolLocation->radius_meters }} meter</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Status:</strong></div>
                        <div class="col-8">
                            @if($schoolLocation->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Warna:</strong></div>
                        <div class="col-8">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle me-2" 
                                     style="width: 20px; height: 20px; background-color: {{ $schoolLocation->color }};"></div>
                                <code>{{ $schoolLocation->color }}</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-info bg-opacity-10">
                <h6 class="mb-0 text-info">
                    <i class="fas fa-lightbulb me-2"></i>Tips Edit Lokasi
                </h6>
            </div>
            <div class="card-body">
                <div class="small text-muted">
                    <ul class="mb-0">
                        <li>Gunakan "Lihat di Maps" untuk memverifikasi lokasi</li>
                        <li>Test lokasi setelah mengubah koordinat</li>
                        <li>Nonaktifkan jika lokasi tidak digunakan lagi</li>
                        <li>Backup koordinat lama sebelum mengubah</li>
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