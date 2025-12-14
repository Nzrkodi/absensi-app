@extends('layouts.admin')

@section('title', 'Siswa')
@section('header', 'Data Siswa')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Daftar Siswa</h3>
        <a href="{{ route('admin.students.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
            + Tambah Siswa
        </a>
    </div>
    
    <!-- Search -->
    <div class="p-6 border-b border-gray-100 bg-gray-50">
        <form action="{{ route('admin.students.index') }}" method="GET" class="flex gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIS..." class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">Cari</button>
        </form>
    </div>

    <div class="p-6">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm border-b">
                    <th class="pb-3">No</th>
                    <th class="pb-3">Kode Siswa</th>
                    <th class="pb-3">Nama</th>
                    <th class="pb-3">Kelas</th>
                    <th class="pb-3">Telepon</th>
                    <th class="pb-3">Status</th>
                    <th class="pb-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                @forelse($students ?? [] as $index => $student)
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                    <td class="py-3">{{ $students->firstItem() + $index }}</td>
                    <td class="py-3">{{ $student->student_code }}</td>
                    <td class="py-3">{{ $student->user->name ?? '-' }}</td>
                    <td class="py-3">{{ $student->class->name ?? '-' }}</td>
                    <td class="py-3">{{ $student->phone ?? '-' }}</td>
                    <td class="py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ $student->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $student->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </td>
                    <td class="py-3">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.students.edit', $student) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a>
                            <form action="{{ route('admin.students.destroy', $student) }}" method="POST" onsubmit="return confirm('Yakin hapus siswa ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-8 text-center text-gray-400">Belum ada data siswa</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($students instanceof \Illuminate\Pagination\LengthAwarePaginator && $students->hasPages())
        <div class="mt-6">
            {{ $students->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
