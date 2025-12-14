@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Card Total Siswa -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Total Siswa</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalStudents ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Card Hadir Hari Ini -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Hadir Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800">{{ $presentToday ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Card Tidak Hadir -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-lg">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Tidak Hadir</p>
                <p class="text-2xl font-bold text-gray-800">{{ $absentToday ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Recent Attendance -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="p-6 border-b border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800">Absensi Terbaru</h3>
    </div>
    <div class="p-6">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm border-b">
                    <th class="pb-3">Nama Siswa</th>
                    <th class="pb-3">Tanggal</th>
                    <th class="pb-3">Status</th>
                    <th class="pb-3">Waktu</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                @forelse($recentAttendances ?? [] as $attendance)
                <tr class="border-b border-gray-50">
                    <td class="py-3">{{ $attendance->student->name }}</td>
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
                    <td class="py-3">{{ $attendance->created_at->format('H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-8 text-center text-gray-400">Belum ada data absensi</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
