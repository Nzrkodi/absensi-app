@extends('layouts.admin')

@section('title', 'Kelola Storage Foto Absen')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Kelola Storage Foto Absen</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-info btn-sm" onclick="refreshStats()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Current Semester Info -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Semester Saat Ini:</strong> {{ $currentSemester }}
                        <br>
                        <small>Foto absen baru akan disimpan dalam folder semester ini</small>
                    </div>

                    <!-- Storage Statistics -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>Statistik Storage</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="storageStatsTable">
                                    <thead>
                                        <tr>
                                            <th>Semester</th>
                                            <th>Jumlah File</th>
                                            <th>Ukuran Total</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalFiles = 0;
                                            $totalSize = 0;
                                        @endphp
                                        @forelse($stats as $semester => $data)
                                            @php
                                                $totalFiles += $data['files_count'];
                                                $totalSize += $data['total_size_mb'];
                                                $isOld = in_array("attendance/photos/{$semester}", $oldFolders);
                                            @endphp
                                            <tr class="{{ $isOld ? 'table-warning' : '' }}">
                                                <td>
                                                    {{ $semester }}
                                                    @if($semester === $currentSemester)
                                                        <span class="badge badge-success">Current</span>
                                                    @endif
                                                </td>
                                                <td>{{ number_format($data['files_count']) }}</td>
                                                <td>{{ $data['total_size_mb'] }} MB</td>
                                                <td>
                                                    @if($isOld)
                                                        <span class="badge badge-warning">Akan Dihapus</span>
                                                    @else
                                                        <span class="badge badge-success">Aktif</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">Tidak ada data storage</td>
                                            </tr>
                                        @endforelse
                                        @if(count($stats) > 0)
                                            <tr class="table-info font-weight-bold">
                                                <td>TOTAL</td>
                                                <td>{{ number_format($totalFiles) }}</td>
                                                <td>{{ round($totalSize, 2) }} MB</td>
                                                <td>-</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Cleanup Section -->
                    @if(count($oldFolders) > 0)
                        <div class="row">
                            <div class="col-12">
                                <div class="card card-warning">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Cleanup Folder Lama
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Ditemukan <strong>{{ count($oldFolders) }}</strong> folder yang lebih lama dari 6 bulan:</p>
                                        <ul>
                                            @foreach($oldFolders as $folder)
                                                @php $folderName = basename($folder); @endphp
                                                <li>
                                                    {{ $folderName }}
                                                    @if(isset($stats[$folderName]))
                                                        ({{ $stats[$folderName]['files_count'] }} files, {{ $stats[$folderName]['total_size_mb'] }} MB)
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                        
                                        <div class="form-group">
                                            <label for="monthsToKeep">Simpan foto untuk berapa bulan terakhir:</label>
                                            <select class="form-control" id="monthsToKeep" style="width: 200px;">
                                                <option value="3">3 bulan</option>
                                                <option value="6" selected>6 bulan</option>
                                                <option value="12">12 bulan</option>
                                                <option value="18">18 bulan</option>
                                                <option value="24">24 bulan</option>
                                            </select>
                                        </div>
                                        
                                        <button type="button" class="btn btn-warning" onclick="cleanupPhotos()">
                                            <i class="fas fa-trash-alt"></i>
                                            Hapus Folder Lama
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            Tidak ada folder lama yang perlu dihapus.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Memproses...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function refreshStats() {
    $('#loadingModal').modal('show');
    
    $.get('{{ route("admin.storage.stats") }}')
        .done(function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Gagal memuat data: ' + response.message);
            }
        })
        .fail(function() {
            alert('Terjadi kesalahan saat memuat data');
        })
        .always(function() {
            $('#loadingModal').modal('hide');
        });
}

function cleanupPhotos() {
    const months = $('#monthsToKeep').val();
    
    if (!confirm(`Apakah Anda yakin ingin menghapus folder foto yang lebih lama dari ${months} bulan? Tindakan ini tidak dapat dibatalkan!`)) {
        return;
    }
    
    $('#loadingModal').modal('show');
    
    $.post('{{ route("admin.storage.cleanup") }}', {
        months: months,
        _token: '{{ csrf_token() }}'
    })
    .done(function(response) {
        if (response.success) {
            alert(response.message);
            location.reload();
        } else {
            alert('Gagal: ' + response.message);
        }
    })
    .fail(function() {
        alert('Terjadi kesalahan saat menghapus folder');
    })
    .always(function() {
        $('#loadingModal').modal('hide');
    });
}
</script>
@endpush