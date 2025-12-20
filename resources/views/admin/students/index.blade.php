@extends('layouts.admin')

@section('title', 'Siswa')
@section('header', 'Data Siswa')

@section('content')
    <!-- Alert Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div
            class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 py-3">
            <h5 class="card-title mb-0">Daftar Siswa</h5>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                + Tambah Siswa
            </button>
        </div>

        <!-- Search & Filter -->
        <div class="card-body bg-light border-bottom">
            <form action="{{ route('admin.students.index') }}" method="GET">
                <div class="row g-2">
                    <div class="col-12 col-md-5">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama atau NISN..." class="form-control form-control">
                    </div>
                    <div class="col-12 col-md-4">
                        <select name="class_id" class="form-select form-select">
                            <option value="">Semua Kelas</option>
                            @foreach ($classes ?? [] as $class)
                                <option value="{{ $class->id }}"
                                    {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-secondary flex-grow-1">Cari</button>
                            <a href="{{ route('admin.students.index') }}"
                                class="btn btn-outline-secondary flex-grow-1">Reset</a>
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
                            <th>No</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>NISN</th>
                            <th>Tempat, Tanggal Lahir</th>
                            <th>Alamat</th>
                            <th>No Handphone</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students ?? [] as $index => $student)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $student->name ?? '-' }}</td>
                                <td>{{ $student->class->name ?? '-' }}</td>
                                <td>{{ $student->nisn ?? '-' }}</td>
                                <td>
                                    @if ($student->birth_place && $student->birth_date)
                                        {{ $student->birth_place }}, {{ $student->birth_date->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $student->address ?? '-' }}</td>
                                <td>{{ $student->phone ?? '-' }}</td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-edit"
                                        data-student-id="{{ $student->id }}">Edit</button>
                                    <form action="{{ route('admin.students.destroy', $student) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="btn btn-outline-danger btn-sm btn-delete">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada data siswa</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="d-md-none">
                @forelse($students ?? [] as $index => $student)
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
                                @if ($student->birth_place && $student->birth_date)
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
                            <button type="button" class="btn btn-outline-primary btn-sm btn-edit"
                                data-student-id="{{ $student->id }}">Edit</button>
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
                                <label for="edit_name" class="form-label">Nama Lengkap <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_nisn" class="form-label">NISN <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_nisn" name="nisn" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_class_id" class="form-label">Kelas <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="edit_class_id" name="class_id" required>
                                    <option value="">Pilih Kelas</option>
                                    @foreach ($classes ?? [] as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_birth_place" class="form-label">Tempat Lahir <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_birth_place" name="birth_place"
                                    required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_birth_date" class="form-label">Tanggal Lahir <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_birth_date" name="birth_date"
                                    required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_phone" class="form-label">No Handphone <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_phone" name="phone" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_status" class="form-label">Status <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="edit_status" name="status" required>
                                    <option value="">Pilih Status</option>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Tidak Aktif</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label for="edit_address" class="form-label">Alamat <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" id="edit_address" name="address" rows="3" required></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" role="status"
                                aria-hidden="true"></span>
                            Update Siswa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel"
        aria-hidden="true">
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
                                <label for="name" class="form-label">Nama Lengkap <span
                                        class="text-danger">*</span></label>
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
                                    @foreach ($classes ?? [] as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="birth_place" class="form-label">Tempat Lahir <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="birth_place" name="birth_place" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="birth_date" class="form-label">Tanggal Lahir <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="birth_date" name="birth_date" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">No Handphone <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phone" name="phone" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label for="address" class="form-label">Alamat</label>
                                <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" role="status"
                                aria-hidden="true"></span>
                            Simpan Siswa
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
                const addSubmitBtn = addForm.querySelector('button[type="submit"]');
                const editSubmitBtn = editForm.querySelector('button[type="submit"]');
                const addSpinner = addSubmitBtn.querySelector('.spinner-border');
                const editSpinner = editSubmitBtn.querySelector('.spinner-border');

                // Show modal if there are validation errors
                @if ($errors->any())
                    const modal = new bootstrap.Modal(document.getElementById('addStudentModal'));
                    modal.show();

                    // Show validation errors
                    @foreach ($errors->all() as $error)
                        // You can customize this to show errors in a toast or alert
                        console.log('{{ $error }}');
                    @endforeach
                @endif

                // Handle add form submission
                addForm.addEventListener('submit', function(e) {
                    // Show loading state
                    addSubmitBtn.disabled = true;
                    addSpinner.classList.remove('d-none');
                    addSubmitBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';
                });

                // Handle edit form submission
                editForm.addEventListener('submit', function(e) {
                    // Show loading state
                    editSubmitBtn.disabled = true;
                    editSpinner.classList.remove('d-none');
                    editSubmitBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengupdate...';
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
                    addSubmitBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span> Simpan Siswa';
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
                    editSubmitBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span> Update Siswa';
                });

                // Handle edit button clicks
                document.querySelectorAll('.btn-edit').forEach(button => {
                    button.addEventListener('click', function() {
                        const studentId = this.getAttribute('data-student-id');

                        // Show loading state on button
                        const originalText = this.innerHTML;
                        this.innerHTML =
                            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
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
                                    document.getElementById('edit_name').value = data.data.name ||
                                        '';
                                    document.getElementById('edit_nisn').value = data.data.nisn ||
                                        '';
                                    document.getElementById('edit_class_id').value = data.data
                                        .class_id || '';
                                    document.getElementById('edit_birth_place').value = data.data
                                        .birth_place || '';
                                    document.getElementById('edit_birth_date').value = data.data
                                        .birth_date || '';
                                    document.getElementById('edit_phone').value = data.data.phone ||
                                        '';
                                    document.getElementById('edit_address').value = data.data
                                        .address || '';
                                    document.getElementById('edit_status').value = data.data
                                        .status || '';

                                    // Set form action
                                    editForm.action =
                                        `{{ route('admin.students.index') }}/${studentId}`;

                                    // Show modal
                                    const modal = new bootstrap.Modal(document.getElementById(
                                        'editStudentModal'));
                                    modal.show();
                                } else {
                                    alert('Gagal memuat data siswa: ' + (data.message ||
                                        'Unknown error'));
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
            });
        </script>
    @endpush
@endsection
