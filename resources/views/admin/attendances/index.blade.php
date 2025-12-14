@extends('layouts.admin')

@section('title', 'Attendance')
@section('header', 'Data Attendance')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Daftar Absensi</h3>
        <a href="{{ route('admin.attendances.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
            + Tambah Absensi
        </a>
    </div>
    
    <!-- Filter -->
    <div class="p-6 border-b border-gray-100 bg-gray-50">
        <form action="{{ route('admin.attendances.index') }}" method="GET" class="flex gap-4">
            <input type="date" name="date" value="{{ request('date') }}" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">Semua Status</option>
                <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Hadir</option>
                <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Terlambat</option>
                <option value="sick" {{ request('status') === 'sick' ? 'selected' : '' }}>Sakit</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">Filter</button>
        </form>
    </div>

    <div class="p-6">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm border-b">
                    <th class="pb-3">No</th>
                    <th class="pb-3">Nama Siswa</th>
                    <th class="pb-3">Tanggal</th>
                    <th class="pb-3">Status</th>
                    <th class="pb-3">Keterangan</th>
                    <th class="pb-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                @forelse($attendances ?? [] as $index => $attendance)
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                    <td class="py-3">{{ $index + 1 }}</td>
                    <td class="py-3">{{ $attendance->student->name ?? '-' }}</td>
                    <td class="py-3">{{ $attendance->date->format('d M Y') }}</td>
                    <td class="py-3">
                        <span class="px-2 py-1 text-xs rounded-full 
                            {{ $attendance->status === 'present' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $attendance->status === 'absent' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $attendance->status === 'late' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $attendance->status === 'sick' ? 'bg-blue-100 text-blue-700' : '' }}">
                            {{ ucfirst($attendance->status) }}
                        </span>
                    </td>
                    <td class="py-3">{{ $attendance->notes ?? '-' }}</td>
                    <td class="py-3">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.attendances.edit', $attendance) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a>
                            <form action="{{ route('admin.attendances.destroy', $attendance) }}" method="POST" onsubmit="return confirm('Yakin hapus?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-400">Belum ada data absensi</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($attendances instanceof \Illuminate\Pagination\LengthAwarePaginator && $attendances->hasPages())
        <div class="mt-6">
            {{ $attendances->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
