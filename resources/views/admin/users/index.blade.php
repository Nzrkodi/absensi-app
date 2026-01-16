@extends('layouts.admin')

@section('title', 'User')
@section('header', 'Data User')

@section('content')
<!-- Alert Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="card-title mb-0">Daftar User</h5>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            + Tambah User
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Password</th>
                        <th>Role</th>
                        <th>Jabatan</th>
                        <th>Mata Pelajaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users ?? [] as $index => $user)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($user->hasAvatar())
                                    <img src="{{ $user->avatar_url }}" 
                                         alt="{{ $user->name }}" 
                                         class="rounded-circle me-2" 
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="avatar-circle bg-primary rounded-circle text-white me-2 d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px; font-size: 16px; font-weight: bold;">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    {{ $user->name }}
                                    @if($user->email === 'aditya.wahyu@smaitpersis.sch.id')
                                        <span class="badge bg-warning text-dark ms-2">
                                            <i class="fas fa-shield-alt"></i> Super Admin
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="text-muted">••••••••</span>
                            <small class="text-muted d-block">Hidden</small>
                        </td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="badge bg-purple text-white" style="background-color: #6f42c1;">{{ ucfirst($user->role ?? 'User') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($user->role ?? 'User') }}</span>
                            @endif
                        </td>
                        <td>{{ $user->position ?? '-' }}</td>
                        <td>{{ $user->subject ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            @if($user->id !== auth()->id() && $user->email !== 'aditya.wahyu@smaitpersis.sch.id')
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete">Hapus</button>
                            </form>
                            @elseif($user->email === 'aditya.wahyu@smaitpersis.sch.id')
                                <span class="badge bg-secondary">
                                    <i class="fas fa-lock"></i> Protected
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Belum ada data user</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users instanceof \Illuminate\Pagination\LengthAwarePaginator && $users->hasPages())
        <div class="mt-3">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
