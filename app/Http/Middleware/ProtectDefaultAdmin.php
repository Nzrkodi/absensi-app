<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectDefaultAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get user ID from route parameter
        $userId = $request->route('user');
        
        // If it's the default admin user, prevent deletion
        if ($userId && is_object($userId)) {
            $user = $userId;
        } else {
            $user = \App\Models\User::find($userId);
        }
        
        if ($user && $user->email === 'aditya.wahyu@smaitpersis.sch.id') {
            if ($request->isMethod('DELETE') || $request->route()->getActionMethod() === 'destroy') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Super Admin default tidak dapat dihapus untuk keamanan sistem.'
                ], 403);
            }
        }
        
        return $next($request);
    }
}