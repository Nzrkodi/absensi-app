@extends('layouts.mobile')

@section('title', 'Absensi Mobile')

@section('content')
<div class="container-fluid px-3 py-2">
    <!-- Header Status -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-1">{{ \Carbon\Carbon::now('Asia/Makassar')->format('d F Y') }}</h6>
                    <small class="text-muted" id="currentTime">{{ \Carbon\Carbon::now('Asia/Makassar')->format('H:i:s') }}</small>
                </div>
                <div class="text-end">
                    <div id="locationStatus" class="small mb-1">
                        <i class="fas fa-spinner fa-spin"></i> Mengecek lokasi...
                    </div>
                    <div id="photoStatus" class="small">
                        <i class="fas fa-camera text-muted"></i> Belum ada foto
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-2 mb-3">
        <div class="col-6">
            <button class="btn btn-primary w-100 py-3" onclick="attendanceMobile.capturePhoto()">
                <i class="fas fa-camera fa-2x d-block mb-2"></i>
                <small>Ambil Foto</small>
            </button>
        </div>
        <div class="col-6">
            <button class="btn btn-info w-100 py-3" onclick="attendanceMobile.initializeGeolocation()">
                <i class="fas fa-map-marker-alt fa-2x d-block mb-2"></i>
                <small>Cek Lokasi</small>
            </button>
        </div>
    </div>

    <!-- Search Student -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" class="form-control" placeholder="Cari NISN atau Nama..." id="studentSearch">
            </div>
        </div>
    </div>

    <!-- Students List -->
    <div id="studentsList">
        @foreach($students as $student)
        @php
            $attendance = $student->attendances->first();
        @endphp
        <div class="card border-0 shadow-sm mb-2 student-card" data-student-name="{{ strtolower($student->name) }}" data-student-nisn="{{ $student->nisn }}">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">{{ $student->name }}</h6>
                        <small class="text-muted">{{ $student->nisn }} • {{ $student->class->name ?? '-' }}</small>
                        
                        @if($attendance)
                        <div class="mt-2">
                            <div class="d-flex gap-3 small">
                                @if($attendance->clock_in)
                                <span><i class="fas fa-sign-in-alt text-success"></i> {{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}</span>
                                @endif
                                @if($attendance->clock_out)
                                <span><i class="fas fa-sign-out-alt text-danger"></i> {{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}</span>
                                @endif
                            </div>
                            <div class="mt-1">
                                {!! $attendance->status_badge !!}
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <div class="text-end">
                        @if(!$attendance || !$attendance->clock_in)
                        <button class="btn btn-success btn-sm mb-1" onclick="clockInStudent({{ $student->id }})">
                            <i class="fas fa-sign-in-alt"></i> Masuk
                        </button>
                        @elseif($attendance->clock_in && !$attendance->clock_out)
                        <button class="btn btn-danger btn-sm mb-1" onclick="clockOutStudent({{ $student->id }})">
                            <i class="fas fa-sign-out-alt"></i> Pulang
                        </button>
                        @endif
                        
                        @if(!$attendance || !in_array($attendance->status, ['sick', 'permission']))
                        <button class="btn btn-warning btn-sm" onclick="noteStudent({{ $student->id }})">
                            <i class="fas fa-sticky-note"></i> Note
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background: rgba(0,0,0,0.5); z-index: 9999;">
    <div class="d-flex align-items-center justify-content-center h-100">
        <div class="text-center text-white">
            <div class="spinner-border mb-3" role="status"></div>
            <div>Memproses absensi...</div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/attendance-mobile.js') }}"></script>
<script>
// Clock In dengan foto dan lokasi
async function clockInStudent(studentId) {
    if (!attendanceMobile.photoBlob) {
        alert('Silakan ambil foto terlebih dahulu');
        return;
    }
    
    document.getElementById('loadingOverlay').classList.remove('d-none');
    await attendanceMobile.submitAttendance(studentId, 'clock-in');
    document.getElementById('loadingOverlay').classList.add('d-none');
}

// Clock Out dengan foto dan lokasi  
async function clockOutStudent(studentId) {
    if (!attendanceMobile.photoBlob) {
        alert('Silakan ambil foto terlebih dahulu');
        return;
    }
    
    document.getElementById('loadingOverlay').classList.remove('d-none');
    await attendanceMobile.submitAttendance(studentId, 'clock-out');
    document.getElementById('loadingOverlay').classList.add('d-none');
}

// Search functionality
document.getElementById('studentSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const studentCards = document.querySelectorAll('.student-card');
    
    studentCards.forEach(card => {
        const name = card.dataset.studentName;
        const nisn = card.dataset.studentNisn;
        
        if (name.includes(searchTerm) || nisn.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});

// Update time every second
setInterval(() => {
    document.getElementById('currentTime').textContent = new Date().toLocaleTimeString('id-ID');
}, 1000);
</script>
@endpush