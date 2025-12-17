<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('role', '!=', 'student')->orderBy('name', 'asc')->get();
        
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,teacher',
            'position' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
        ]);

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'position' => $request->position,
                'subject' => $request->subject,
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil ditambahkan');
                
        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Gagal menambahkan user. ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,teacher',
            'position' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
        ]);

        try {
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'position' => $request->position,
                'subject' => $request->subject,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil diupdate');
                
        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Gagal mengupdate user. ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent deleting current user
            if ($user->id === auth()->id()) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'Tidak dapat menghapus akun sendiri');
            }
            
            $user->delete();
            
            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil dihapus');
                
        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Gagal menghapus user. ' . $e->getMessage());
        }
    }
}
