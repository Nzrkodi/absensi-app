class AttendanceMobile {
    constructor() {
        this.currentPosition = null;
        this.photoBlob = null;
        this.schoolLocations = []; // Will be loaded from API
        this.validLocation = null;
        
        this.loadSchoolLocations();
        this.initializeGeolocation();
    }

    // Load school locations from API
    async loadSchoolLocations() {
        try {
            console.log('Loading school locations...');
            const response = await fetch('/api/school-locations/active');
            const result = await response.json();
            
            console.log('School locations response:', result);
            
            if (result.success) {
                this.schoolLocations = result.locations;
                console.log('Loaded school locations:', this.schoolLocations);
            } else {
                console.error('Failed to load school locations:', result);
            }
        } catch (error) {
            console.error('Failed to load school locations:', error);
        }
    }

    // Ambil lokasi GPS
    initializeGeolocation() {
        if (navigator.geolocation) {
            // Update location status
            const locationStatus = document.getElementById('locationStatus');
            if (locationStatus) {
                locationStatus.innerHTML = `
                    <div class="spinner-border spinner-border-sm me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="text-muted">Mengecek lokasi...</span>
                `;
            }
            
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.currentPosition = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    };
                    console.log('Current position:', this.currentPosition);
                    this.validateLocation();
                    
                    // Refresh location every 30 seconds
                    setInterval(() => {
                        this.refreshLocation();
                    }, 30000);
                },
                (error) => {
                    console.error('Geolocation error:', error);
                    this.showLocationError(error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        } else {
            this.showLocationError({ code: 0, message: 'Browser tidak mendukung GPS' });
        }
    }
    
    // Refresh location
    refreshLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.currentPosition = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    };
                    this.validateLocation();
                },
                (error) => {
                    console.error('Location refresh error:', error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0
                }
            );
        }
    }

    // Validasi jarak dari sekolah
    async validateLocation() {
        if (!this.currentPosition) {
            console.log('No current position available');
            return false;
        }
        
        console.log('Validating location:', this.currentPosition);
        
        try {
            const response = await fetch('/api/school-locations/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    latitude: this.currentPosition.latitude,
                    longitude: this.currentPosition.longitude
                })
            });
            
            const result = await response.json();
            console.log('Validation result:', result);
            
            // Update UI
            const locationStatus = document.getElementById('locationStatus');
            if (locationStatus) {
                if (result.valid) {
                    locationStatus.innerHTML = `
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <span class="text-success">Lokasi valid - ${result.location.name} (${result.distance}m)</span>
                    `;
                    this.validLocation = result.location;
                } else {
                    const nearest = result.nearest_location;
                    locationStatus.innerHTML = `
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        <span class="text-danger">Lokasi terlalu jauh${nearest ? ` - ${nearest.distance}m dari ${nearest.name}` : ''}</span>
                    `;
                    this.validLocation = null;
                }
            }
            
            return result.valid;
        } catch (error) {
            console.error('Location validation error:', error);
            const locationStatus = document.getElementById('locationStatus');
            if (locationStatus) {
                locationStatus.innerHTML = `
                    <i class="fas fa-exclamation-circle text-warning me-2"></i>
                    <span class="text-warning">Gagal memvalidasi lokasi</span>
                `;
            }
            return false;
        }
    }

    // Hitung jarak menggunakan Haversine formula
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // Earth's radius in meters
        const φ1 = lat1 * Math.PI/180;
        const φ2 = lat2 * Math.PI/180;
        const Δφ = (lat2-lat1) * Math.PI/180;
        const Δλ = (lon2-lon1) * Math.PI/180;

        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                Math.cos(φ1) * Math.cos(φ2) *
                Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

        return R * c;
    }

    // Ambil foto dari kamera
    async capturePhoto() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: 'user', // Front camera untuk selfie
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                } 
            });
            
            const video = document.createElement('video');
            video.srcObject = stream;
            video.play();
            
            // Show camera modal
            this.showCameraModal(video, stream);
            
        } catch (error) {
            console.error('Camera error:', error);
            alert('Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.');
        }
    }

    // Tampilkan modal kamera
    showCameraModal(video, stream) {
        const modal = document.createElement('div');
        modal.className = 'modal fade show';
        modal.style.display = 'block';
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ambil Foto Absensi</h5>
                        <button type="button" class="btn-close" onclick="this.closest('.modal').remove()"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div id="cameraContainer" class="mb-3">
                            <video id="cameraVideo" width="100%" height="300" autoplay></video>
                            <canvas id="photoCanvas" width="640" height="480" style="display: none;"></canvas>
                        </div>
                        <div id="photoPreview" style="display: none;">
                            <img id="capturedPhoto" width="100%" height="300" class="rounded">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').remove()">Batal</button>
                        <button type="button" id="captureBtn" class="btn btn-primary">
                            <i class="fas fa-camera"></i> Ambil Foto
                        </button>
                        <button type="button" id="retakeBtn" class="btn btn-warning" style="display: none;">
                            <i class="fas fa-redo"></i> Ambil Ulang
                        </button>
                        <button type="button" id="usePhotoBtn" class="btn btn-success" style="display: none;">
                            <i class="fas fa-check"></i> Gunakan Foto
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        const videoElement = modal.querySelector('#cameraVideo');
        videoElement.srcObject = stream;
        
        // Event listeners
        modal.querySelector('#captureBtn').onclick = () => this.takePhoto(videoElement, modal);
        modal.querySelector('#retakeBtn').onclick = () => this.retakePhoto(videoElement, modal);
        modal.querySelector('#usePhotoBtn').onclick = () => this.usePhoto(modal, stream);
    }

    // Ambil foto
    takePhoto(video, modal) {
        const canvas = modal.querySelector('#photoCanvas');
        const context = canvas.getContext('2d');
        
        context.drawImage(video, 0, 0, 640, 480);
        
        canvas.toBlob((blob) => {
            this.photoBlob = blob;
            
            // Show preview
            const preview = modal.querySelector('#photoPreview');
            const img = modal.querySelector('#capturedPhoto');
            img.src = URL.createObjectURL(blob);
            
            modal.querySelector('#cameraContainer').style.display = 'none';
            preview.style.display = 'block';
            
            modal.querySelector('#captureBtn').style.display = 'none';
            modal.querySelector('#retakeBtn').style.display = 'inline-block';
            modal.querySelector('#usePhotoBtn').style.display = 'inline-block';
        }, 'image/jpeg', 0.8);
    }

    // Ambil ulang foto
    retakePhoto(video, modal) {
        modal.querySelector('#cameraContainer').style.display = 'block';
        modal.querySelector('#photoPreview').style.display = 'none';
        
        modal.querySelector('#captureBtn').style.display = 'inline-block';
        modal.querySelector('#retakeBtn').style.display = 'none';
        modal.querySelector('#usePhotoBtn').style.display = 'none';
    }

    // Gunakan foto
    usePhoto(modal, stream) {
        // Stop camera stream
        stream.getTracks().forEach(track => track.stop());
        
        // Update UI to show photo is captured
        const photoStatus = document.getElementById('photoStatus');
        if (photoStatus) {
            photoStatus.innerHTML = '<i class="fas fa-check-circle text-success"></i> Foto berhasil diambil';
        }
        
        modal.remove();
    }

    // Submit absensi dengan foto dan lokasi
    async submitAttendance(studentId, mode = 'clock_in') {
        if (!this.validateLocation()) {
            if (!confirm('Lokasi Anda terlalu jauh dari sekolah. Tetap lanjutkan absensi?')) {
                return;
            }
        }

        const formData = new FormData();
        formData.append('clock_mode', 'current');
        
        if (this.photoBlob) {
            formData.append('photo', this.photoBlob, 'attendance.jpg');
        }
        
        if (this.currentPosition) {
            formData.append('latitude', this.currentPosition.latitude);
            formData.append('longitude', this.currentPosition.longitude);
        }

        try {
            const response = await fetch(`/admin/attendance/${studentId}/${mode}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();
            
            if (result.success) {
                this.showSuccess(result.message);
                location.reload(); // Refresh halaman
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            console.error('Submit error:', error);
            this.showError('Terjadi kesalahan saat mengirim data');
        }
    }

    // Helper methods
    showLocationError(error) {
        const locationStatus = document.getElementById('locationStatus');
        if (locationStatus) {
            let errorMessage = 'Tidak dapat mengakses lokasi';
            
            if (error) {
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = 'Izin lokasi ditolak. Aktifkan izin lokasi di pengaturan browser.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = 'Informasi lokasi tidak tersedia. Pastikan GPS aktif.';
                        break;
                    case error.TIMEOUT:
                        errorMessage = 'Waktu permintaan lokasi habis. Coba lagi.';
                        break;
                    default:
                        errorMessage = error.message || 'Tidak dapat mengakses lokasi';
                }
            }
            
            locationStatus.innerHTML = `
                <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                <span class="text-danger">${errorMessage}</span>
            `;
        }
    }

    showSuccess(message) {
        // Implementasi toast notification
        alert('✅ ' + message);
    }

    showError(message) {
        // Implementasi toast notification
        alert('❌ ' + message);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.attendanceMobile = new AttendanceMobile();
});