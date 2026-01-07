@extends('layouts.admin')

@section('title', 'Siswa')
@section('header', 'Data Siswa')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 py-3">
        <h5 class="card-title mb-0">Daftar Siswa</h5>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import"></i> Import Data
            </button>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                + Tambah Siswa
            </button>
            @if($students->total() > 0)
            <div class="btn-group">
                <button type="button" class="btn btn-warning btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-tasks"></i> Bulk Actions
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="showBulkActions('active')">Set Aktif</a></li>
                    <li><a class="dropdown-item" href="#" onclick="showBulkActions('inactive')">Set Tidak Aktif</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="showBulkActions('delete')">Hapus Terpilih</a></li>
                </ul>
            </div>
            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteAllModal">
                <i class="fas fa-trash-alt"></i> Hapus Semua
            </button>
            @endif
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card-body bg-light border-bottom">
        <form action="{{ route('admin.students.index') }}" method="GET">
            <div class="row g-2">
                <div class="col-12 col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NISN..." class="form-control form-control">
                </div>
                <div class="col-12 col-md-4">
                    <select name="class_id" class="form-select form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($classes ?? [] as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-secondary flex-grow-1">Cari</button>
                        <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary flex-grow-1">Reset</a>
                    </div>
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
                        <th>
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>NISN</th>
                        <th>Tempat, Tanggal Lahir</th>
                        <th>Alamat</th>
                        <th>No Handphone</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $index => $student)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input student-checkbox" value="{{ $student->id }}">
                        </td>
                        <td>{{ ($students->currentPage() - 1) * $students->perPage() + $index + 1 }}</td>
                        <td>{{ $student->name ?? '-' }}</td>
                        <td>{{ $student->class->name ?? '-' }}</td>
                        <td>{{ $student->nisn ?? '-' }}</td>
                        <td>
                            @if($student->birth_place && $student->birth_date)
                                {{ $student->birth_place }}, {{ $student->birth_date->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $student->address ?? '-' }}</td>
                        <td>{{ $student->phone ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $student->status === 'active' ? 'success' : 'secondary' }}">
                                {{ $student->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-outline-primary btn-sm btn-edit" data-student-id="{{ $student->id }}">Edit</button>
                            <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm btn-delete">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">Belum ada data siswa</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="d-md-none">
            @forelse($students as $index => $student)
            <div class="border-bottom p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="mb-1">{{ $student->name ?? '-' }}</h6>
                        <small class="text-muted">NISN: {{ $student->nisn ?? '-' }}</small>
                    </div>
                </div>
                <div class="row g-2 small text-muted mb-2">
                    <div class="col-6">
                        <strong>Kelas:</strong> {{ $student->class->name ?? '-' }}
                    </div>
                    <div class="col-6">
                        <strong>Telepon:</strong> {{ $student->phone ?? '-' }}
                    </div>
                    <div class="col-12">
                        <strong>TTL:</strong> 
                        @if($student->birth_place && $student->birth_date)
                            {{ $student->birth_place }}, {{ $student->birth_date->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </div>
                    <div class="col-12">
                        <strong>Alamat:</strong> {{ $student->address ?? '-' }}
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm btn-edit" data-student-id="{{ $student->id }}">Edit</button>
                    <form action="{{ route('admin.students.destroy', $student) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm btn-delete">Hapus</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4">Belum ada data siswa</div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($students->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-center">
                {{ $students->appends(request()->query())->links() }}
            </div>
        </div>
        @endif

    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Data Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Panduan Import:</h6>
                        <ul class="mb-0 small">
                            <li>File harus berformat Excel (.xlsx, .xls) atau CSV</li>
                            <li>Maksimal ukuran file: 2MB</li>
                            <li>Gunakan template yang disediakan untuk format yang benar</li>
                            <li>Pastikan kolom header sesuai: Nama, NISN, Kelas, Tempat Lahir, Tanggal Lahir, No Handphone, Alamat</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <label for="import_file" class="form-label">Pilih File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="import_file" name="file" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">Format yang didukung: Excel (.xlsx, .xls) dan CSV</div>
                    </div>
                    
                    <div class="mb-3">
                        <a href="{{ route('admin.students.template') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download"></i> Download Template
                        </a>
                        <small class="text-muted d-block mt-1">Download template untuk melihat format yang benar</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        <i class="fas fa-file-import"></i> Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStudentModalLabel">Edit Data Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="editStudentForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_nisn" class="form-label">NISN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_nisn" name="nisn" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_class_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_class_id" name="class_id" required>
                                <option value="">Pilih Kelas</option>
                                @foreach($classes ?? [] as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_birth_place" class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_birth_place" name="birth_place" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_birth_date" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_birth_date" name="birth_date" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_phone" class="form-label">No Handphone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_phone" name="phone" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="">Pilih Status</option>
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label for="edit_address" class="form-label">Alamat <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit_address" name="address" rows="3" required></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Update Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStudentModalLabel">Tambah Siswa Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.students.store') }}" method="POST" id="addStudentForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="nisn" class="form-label">NISN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nisn" name="nisn" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="class_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select class="form-select" id="class_id" name="class_id" required>
                                <option value="">Pilih Kelas</option>
                                @foreach($classes ?? [] as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="birth_place" class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="birth_place" name="birth_place" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="birth_date" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="birth_date" name="birth_date" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">No Handphone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="phone" name="phone" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="">Pilih Status</option>
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Alamat <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Simpan Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete All Modal -->
<div class="modal fade" id="deleteAllModal" tabindex="-1" aria-labelledby="deleteAllModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteAllModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> Hapus Semua Data Siswa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.students.delete-all') }}" method="POST" id="deleteAllForm">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-exclamation-triangle"></i> PERINGATAN!</h6>
                        <p class="mb-2">Tindakan ini akan menghapus <strong>SEMUA DATA SISWA</strong> dan <strong>DATA ABSENSI TERKAIT</strong> secara permanen!</p>
                        <ul class="mb-0 small">
                            <li>Total siswa yang akan dihapus: <strong>{{ $students->total() }} siswa</strong></li>
                            <li>Semua data absensi siswa juga akan terhapus</li>
                            <li>Tindakan ini <strong>TIDAK DAPAT DIBATALKAN</strong></li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirmation_text" class="form-label">
                            Untuk melanjutkan, ketik: <strong class="text-danger">HAPUS SEMUA DATA</strong>
                        </label>
                        <input type="text" class="form-control" id="confirmation_text" name="confirmation" 
                               placeholder="Ketik: HAPUS SEMUA DATA" required>
                        <div class="form-text text-muted">Pastikan mengetik dengan benar dan huruf kapital</div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="understand_risk" required>
                        <label class="form-check-label text-danger" for="understand_risk">
                            Saya memahami bahwa tindakan ini akan menghapus semua data siswa secara permanen
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" id="deleteAllBtn" disabled>
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        <i class="fas fa-trash-alt"></i> Hapus Semua Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1" aria-labelledby="bulkActionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkActionModalLabel">Bulk Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkActionForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="bulkActionContent"></div>
                    <div class="mt-3">
                        <strong>Siswa yang dipilih:</strong>
                        <div id="selectedStudentsList" class="mt-2 p-2 bg-light rounded small"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="bulkActionBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Proses
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const addForm = document.getElementById('addStudentForm');
    const editForm = document.getElementById('editStudentForm');
    const importForm = document.getElementById('importForm');
    const deleteAllForm = document.getElementById('deleteAllForm');
    const addSubmitBtn = addForm.querySelector('button[type="submit"]');
    const editSubmitBtn = editForm.querySelector('button[type="submit"]');
    const importSubmitBtn = importForm.querySelector('button[type="submit"]');
    const deleteAllBtn = document.getElementById('deleteAllBtn');
    const addSpinner = addSubmitBtn.querySelector('.spinner-border');
    const editSpinner = editSubmitBtn.querySelector('.spinner-border');
    const importSpinner = importSubmitBtn.querySelector('.spinner-border');
    const deleteAllSpinner = deleteAllBtn.querySelector('.spinner-border');
    
    // Loading overlay function
    function showLoadingOverlay(message = 'Memuat data...') {
        const overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.innerHTML = `
            <div class="d-flex justify-content-center align-items-center h-100">
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted">${message}</p>
                </div>
            </div>
        `;
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            display: flex;
        `;
        document.body.appendChild(overlay);
    }
    
    function hideLoadingOverlay() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.remove();
        }
    }
    
    // Show modal if there are validation errors
    @if($errors->any())
        const modal = new bootstrap.Modal(document.getElementById('addStudentModal'));
        modal.show();
        
        // Show validation errors
        @foreach($errors->all() as $error)
            console.log('{{ $error }}');
        @endforeach
    @endif
    
    // Handle delete all form validation
    const confirmationInput = document.getElementById('confirmation_text');
    const understandCheckbox = document.getElementById('understand_risk');
    
    function validateDeleteAllForm() {
        if (confirmationInput && understandCheckbox && deleteAllBtn) {
            const isTextCorrect = confirmationInput.value === 'HAPUS SEMUA DATA';
            const isCheckboxChecked = understandCheckbox.checked;
            deleteAllBtn.disabled = !(isTextCorrect && isCheckboxChecked);
        }
    }
    
    if (confirmationInput) {
        confirmationInput.addEventListener('input', validateDeleteAllForm);
    }
    if (understandCheckbox) {
        understandCheckbox.addEventListener('change', validateDeleteAllForm);
    }
    
    // Handle delete all form submission
    if (deleteAllForm) {
        deleteAllForm.addEventListener('submit', function(e) {
            if (confirmationInput.value !== 'HAPUS SEMUA DATA') {
                e.preventDefault();
                alert('Konfirmasi teks tidak sesuai. Ketik "HAPUS SEMUA DATA" dengan benar.');
                return;
            }
            
            if (!understandCheckbox.checked) {
                e.preventDefault();
                alert('Anda harus mencentang kotak konfirmasi untuk melanjutkan.');
                return;
            }
            
            showLoadingOverlay('Menghapus semua data siswa... Mohon tunggu.');
            deleteAllBtn.disabled = true;
            if (deleteAllSpinner) {
                deleteAllSpinner.classList.remove('d-none');
            }
            deleteAllBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menghapus...';
        });
    }
    
    // Reset delete all form when modal is closed
    const deleteAllModal = document.getElementById('deleteAllModal');
    if (deleteAllModal) {
        deleteAllModal.addEventListener('hidden.bs.modal', function() {
            if (deleteAllForm) {
                deleteAllForm.reset();
            }
            if (deleteAllBtn) {
                deleteAllBtn.disabled = true;
                deleteAllBtn.innerHTML = '<i class="fas fa-trash-alt"></i> Hapus Semua Data';
            }
            if (deleteAllSpinner) {
                deleteAllSpinner.classList.add('d-none');
            }
        });
    }
    
    // Handle import form submission
    importForm.addEventListener('submit', function(e) {
        showLoadingOverlay('Mengimport data siswa... Mohon tunggu sebentar.');
        importSubmitBtn.disabled = true;
        importSpinner.classList.remove('d-none');
        importSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengimport...';
    });
    
    // Handle add form submission
    addForm.addEventListener('submit', function(e) {
        showLoadingOverlay('Menyimpan data siswa...');
        addSubmitBtn.disabled = true;
        addSpinner.classList.remove('d-none');
        addSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';
    });
    
    // Handle edit form submission
    editForm.addEventListener('submit', function(e) {
        showLoadingOverlay('Mengupdate data siswa...');
        editSubmitBtn.disabled = true;
        editSpinner.classList.remove('d-none');
        editSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengupdate...';
    });
    
    // Reset import form when modal is closed
    const importModal = document.getElementById('importModal');
    importModal.addEventListener('hidden.bs.modal', function() {
        importForm.reset();
        
        // Reset submit button
        importSubmitBtn.disabled = false;
        importSpinner.classList.add('d-none');
        importSubmitBtn.innerHTML = '<i class="fas fa-file-import"></i> Import Data';
    });
    
    // Reset add form when modal is closed
    const addModal = document.getElementById('addStudentModal');
    addModal.addEventListener('hidden.bs.modal', function() {
        addForm.reset();
        addForm.classList.remove('was-validated');
        
        // Clear all validation states
        const inputs = addForm.querySelectorAll('.form-control, .form-select');
        inputs.forEach(input => {
            input.classList.remove('is-invalid', 'is-valid');
        });
        
        // Reset submit button
        addSubmitBtn.disabled = false;
        addSpinner.classList.add('d-none');
        addSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span> Simpan Siswa';
    });
    
    // Reset edit form when modal is closed
    const editModal = document.getElementById('editStudentModal');
    editModal.addEventListener('hidden.bs.modal', function() {
        editForm.reset();
        editForm.classList.remove('was-validated');
        
        // Clear all validation states
        const inputs = editForm.querySelectorAll('.form-control, .form-select');
        inputs.forEach(input => {
            input.classList.remove('is-invalid', 'is-valid');
        });
        
        // Reset submit button
        editSubmitBtn.disabled = false;
        editSpinner.classList.add('d-none');
        editSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span> Update Siswa';
    });
    
    // Handle edit button clicks
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function() {
            const studentId = this.getAttribute('data-student-id');
            
            // Show loading state on button
            const originalText = this.innerHTML; 
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            this.disabled = true;
            
            // Fetch student data
            fetch(`{{ route('admin.students.index') }}/${studentId}/edit`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate form fields
                    document.getElementById('edit_name').value = data.data.name || '';
                    document.getElementById('edit_nisn').value = data.data.nisn || '';
                    document.getElementById('edit_class_id').value = data.data.class_id || '';
                    document.getElementById('edit_birth_place').value = data.data.birth_place || '';
                    document.getElementById('edit_birth_date').value = data.data.birth_date || '';
                    document.getElementById('edit_phone').value = data.data.phone || '';
                    document.getElementById('edit_address').value = data.data.address || '';
                    document.getElementById('edit_status').value = data.data.status || '';
                    
                    // Set form action
                    editForm.action = `{{ route('admin.students.index') }}/${studentId}`;
                    
                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('editStudentModal'));
                    modal.show();
                } else {
                    alert('Gagal memuat data siswa: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memuat data siswa');
            })
            .finally(() => {
                // Reset button state
                this.innerHTML = originalText;
                this.disabled = false;
            });
        });
    });
    
    // Auto-generate NISN based on name (optional)
    const nameInput = document.getElementById('name');
    const nisnInput = document.getElementById('nisn');
    
    nameInput.addEventListener('input', function() {
        if (!nisnInput.value) {
            // Generate simple NISN from name (you can customize this logic)
            const name = this.value.trim();
            if (name) {
                const nisn = name.toLowerCase()
                    .replace(/[^a-z0-9]/g, '')
                    .substring(0, 8) + Math.floor(Math.random() * 1000);
                nisnInput.value = nisn.toUpperCase();
            }
        }
    });
    
    // Bulk selection functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const studentCheckboxes = document.querySelectorAll('.student-checkbox');
    
    // Select/deselect all
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            studentCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
    
    // Update select all checkbox when individual checkboxes change
    studentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
            const totalCount = studentCheckboxes.length;
            
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = checkedCount === totalCount;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
            }
        });
    });
});

// Bulk actions functions (outside DOMContentLoaded)
function showBulkActions(action) {
    const selectedCheckboxes = document.querySelectorAll('.student-checkbox:checked');
    
    if (selectedCheckboxes.length === 0) {
        alert('Pilih minimal satu siswa untuk melakukan bulk action');
        return;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('bulkActionModal'));
    const form = document.getElementById('bulkActionForm');
    const content = document.getElementById('bulkActionContent');
    const studentsList = document.getElementById('selectedStudentsList');
    const submitBtn = document.getElementById('bulkActionBtn');
    
    // Clear previous content
    content.innerHTML = '';
    studentsList.innerHTML = '';
    
    // Get selected student names
    const selectedStudents = [];
    selectedCheckboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const studentName = row.cells[2].textContent.trim(); // Name column
        selectedStudents.push(studentName);
        
        // Add hidden input for student ID
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'student_ids[]';
        hiddenInput.value = checkbox.value;
        form.appendChild(hiddenInput);
    });
    
    // Display selected students
    studentsList.innerHTML = selectedStudents.join(', ');
    
    // Configure modal based on action
    if (action === 'delete') {
        document.getElementById('bulkActionModalLabel').textContent = 'Hapus Siswa Terpilih';
        content.innerHTML = `
            <div class="alert alert-danger">
                <h6><i class="fas fa-exclamation-triangle"></i> Peringatan!</h6>
                <p class="mb-0">Anda akan menghapus <strong>${selectedCheckboxes.length} siswa</strong> beserta data absensi terkait. Tindakan ini tidak dapat dibatalkan!</p>
            </div>
        `;
        form.action = '{{ route("admin.students.bulk-delete") }}';
        submitBtn.className = 'btn btn-danger';
        submitBtn.innerHTML = '<i class="fas fa-trash-alt"></i> Hapus Siswa';
    } else if (action === 'active' || action === 'inactive') {
        const statusText = action === 'active' ? 'Aktif' : 'Tidak Aktif';
        document.getElementById('bulkActionModalLabel').textContent = `Set Status ${statusText}`;
        content.innerHTML = `
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle"></i> Konfirmasi</h6>
                <p class="mb-0">Anda akan mengubah status <strong>${selectedCheckboxes.length} siswa</strong> menjadi <strong>${statusText}</strong>.</p>
            </div>
            <input type="hidden" name="status" value="${action}">
        `;
        form.action = '{{ route("admin.students.bulk-update-status") }}';
        submitBtn.className = 'btn btn-primary';
        submitBtn.innerHTML = `<i class="fas fa-check"></i> Update Status`;
    }
    
    modal.show();
}

// Handle bulk action form submission
document.getElementById('bulkActionForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('bulkActionBtn');
    const spinner = submitBtn.querySelector('.spinner-border');
    
    submitBtn.disabled = true;
    if (spinner) {
        spinner.classList.remove('d-none');
    }
});

// Reset bulk action form when modal is closed
document.getElementById('bulkActionModal').addEventListener('hidden.bs.modal', function() {
    const form = document.getElementById('bulkActionForm');
    const hiddenInputs = form.querySelectorAll('input[name="student_ids[]"]');
    const statusInput = form.querySelector('input[name="status"]');
    
    // Remove dynamically added inputs
    hiddenInputs.forEach(input => input.remove());
    if (statusInput) statusInput.remove();
    
    // Reset button
    const submitBtn = document.getElementById('bulkActionBtn');
    submitBtn.disabled = false;
    submitBtn.className = 'btn btn-primary';
    submitBtn.innerHTML = 'Proses';
    
    const spinner = submitBtn.querySelector('.spinner-border');
    if (spinner) {
        spinner.classList.add('d-none');
    }
});
</script>
@endpush
@endsection
