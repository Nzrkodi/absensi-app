@extends('layouts.admin')

@section('title', 'Guru')
@section('header', 'Data Guru')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 py-3">
        <h5 class="card-title mb-0">Daftar Guru</h5>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                + Tambah Guru
            </button>
            @if($teachers->total() > 0)
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
            @endif
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card-body bg-light border-bottom">
        <form action="{{ route('admin.teachers.index') }}" method="GET" id="filterForm">
            <div class="row g-2">
                <div class="col-12 col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="form-control">
                </div>
                <div class="col-12 col-md-4">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-undo me-1"></i> Reset Filter
                    </a>
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
                        <th>Email</th>
                        <th>Jenis Kelamin</th>
                        <th>No HP</th>
                        <th>Jabatan</th>
                        <th>Mata Pelajaran</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $index => $teacher)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input teacher-checkbox" value="{{ $teacher->id }}">
                        </td>
                        <td>{{ ($teachers->currentPage() - 1) * $teachers->perPage() + $index + 1 }}</td>
                        <td>{{ $teacher->name }}</td>
                        <td>{{ $teacher->email }}</td>
                        <td>{{ $teacher->jenis_kelamin_label }}</td>
                        <td>{{ $teacher->nomor_hp ?? '-' }}</td>
                        <td>{{ $teacher->jabatan ?? '-' }}</td>
                        <td>{{ $teacher->mata_pelajaran ?? '-' }}</td>
                        <td>{!! $teacher->status_badge !!}</td>
                        <td>
                            <button type="button" class="btn btn-outline-primary btn-sm btn-edit" data-teacher-id="{{ $teacher->id }}">Edit</button>
                            <form action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm btn-delete">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">Belum ada data guru</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="d-md-none">
            @forelse($teachers as $index => $teacher)
            <div class="border-bottom p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="mb-1">{{ $teacher->name }}</h6>
                        <small class="text-muted">{{ $teacher->email }}</small>
                    </div>
                    {!! $teacher->status_badge !!}
                </div>
                <div class="row g-2 small text-muted mb-2">
                    <div class="col-6">
                        <strong>Email:</strong> {{ $teacher->email }}
                    </div>
                    <div class="col-6">
                        <strong>No HP:</strong> {{ $teacher->nomor_hp ?? '-' }}
                    </div>
                    <div class="col-6">
                        <strong>Jabatan:</strong> {{ $teacher->jabatan ?? '-' }}
                    </div>
                    <div class="col-6">
                        <strong>Mapel:</strong> {{ $teacher->mata_pelajaran ?? '-' }}
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm btn-edit" data-teacher-id="{{ $teacher->id }}">Edit</button>
                    <form action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm btn-delete">Hapus</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4">Belum ada data guru</div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($teachers->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $teachers->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add Teacher Modal -->
<div class="modal fade" id="addTeacherModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.teachers.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Guru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="jenis_kelamin" class="form-select" required>
                                <option value="">Pilih...</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No HP</label>
                            <input type="text" name="nomor_hp" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jabatan</label>
                            <input type="text" name="jabatan" class="form-control" placeholder="Contoh: Guru Tetap">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mata Pelajaran</label>
                            <input type="text" name="mata_pelajaran" class="form-control" placeholder="Contoh: Matematika">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Teacher Modal -->
<div class="modal fade" id="editTeacherModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editTeacherForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Guru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="jenis_kelamin" id="edit_jenis_kelamin" class="form-select" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No HP</label>
                            <input type="text" name="nomor_hp" id="edit_nomor_hp" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jabatan</label>
                            <input type="text" name="jabatan" id="edit_jabatan" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mata Pelajaran</label>
                            <input type="text" name="mata_pelajaran" id="edit_mata_pelajaran" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" id="edit_address" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto submit filter
    $('select[name="status"]').on('change', function() {
        $('#filterForm').submit();
    });

    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.teacher-checkbox').prop('checked', $(this).prop('checked'));
    });

    // Edit teacher
    $('.btn-edit').on('click', function() {
        const teacherId = $(this).data('teacher-id');
        
        $.ajax({
            url: `/admin/teachers/${teacherId}/edit`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const teacher = response.data;
                    $('#editTeacherForm').attr('action', `/admin/teachers/${teacherId}`);
                    $('#edit_name').val(teacher.name);
                    $('#edit_email').val(teacher.email);
                    $('#edit_jenis_kelamin').val(teacher.jenis_kelamin);
                    $('#edit_nomor_hp').val(teacher.nomor_hp);
                    $('#edit_jabatan').val(teacher.jabatan);
                    $('#edit_mata_pelajaran').val(teacher.mata_pelajaran);
                    $('#edit_address').val(teacher.address);
                    $('#edit_status').val(teacher.status);
                    $('#editTeacherModal').modal('show');
                }
            },
            error: function() {
                alert('Gagal memuat data guru');
            }
        });
    });

    // Delete confirmation
    $('.btn-delete').on('click', function(e) {
        e.preventDefault();
        if (confirm('Yakin ingin menghapus guru ini?')) {
            $(this).closest('form').submit();
        }
    });
});

function showBulkActions(action) {
    const selectedIds = $('.teacher-checkbox:checked').map(function() {
        return $(this).val();
    }).get();

    if (selectedIds.length === 0) {
        alert('Pilih minimal satu guru');
        return;
    }

    if (action === 'delete') {
        if (!confirm(`Yakin ingin menghapus ${selectedIds.length} guru?`)) {
            return;
        }
        
        const form = $('<form>', {
            method: 'POST',
            action: '{{ route("admin.teachers.bulk-delete") }}'
        });
        
        form.append('{{ csrf_field() }}');
        selectedIds.forEach(id => {
            form.append($('<input>', { type: 'hidden', name: 'teacher_ids[]', value: id }));
        });
        
        $('body').append(form);
        form.submit();
    } else {
        if (!confirm(`Yakin ingin mengubah status ${selectedIds.length} guru menjadi ${action === 'active' ? 'aktif' : 'tidak aktif'}?`)) {
            return;
        }
        
        const form = $('<form>', {
            method: 'POST',
            action: '{{ route("admin.teachers.bulk-update-status") }}'
        });
        
        form.append('{{ csrf_field() }}');
        form.append($('<input>', { type: 'hidden', name: 'status', value: action }));
        selectedIds.forEach(id => {
            form.append($('<input>', { type: 'hidden', name: 'teacher_ids[]', value: id }));
        });
        
        $('body').append(form);
        form.submit();
    }
}
</script>
@endpush
