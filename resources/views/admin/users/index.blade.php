@extends('layouts.admin')

@section('title', 'User')
@section('header', 'Data User')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Daftar User</h3>
        <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
            + Tambah User
        </a>
    </div>

    <div class="p-6">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm border-b">
                    <th class="pb-3">No</th>
                    <th class="pb-3">Nama</th>
                    <th class="pb-3">Email</th>
                    <th class="pb-3">Role</th>
                    <th class="pb-3">Dibuat</th>
                    <th class="pb-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                @forelse($users ?? [] as $index => $user)
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                    <td class="py-3">{{ $index + 1 }}</td>
                    <td class="py-3">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            {{ $user->name }}
                        </div>
                    </td>
                    <td class="py-3">{{ $user->email }}</td>
                    <td class="py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="py-3">{{ $user->created_at->format('d M Y') }}</td>
                    <td class="py-3">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Yakin hapus user ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-400">Belum ada data user</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($users instanceof \Illuminate\Pagination\LengthAwarePaginator && $users->hasPages())
        <div class="mt-6">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
