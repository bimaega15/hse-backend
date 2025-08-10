<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthenticationController extends Controller
{
    public function index(): View
    {
        return view('auth.authentication.login');
    }

    public function login(Request $request): JsonResponse
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 6 karakter',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cek kredensial
        $credentials = $request->only('email', 'password');
        
        // Cek apakah user exists dan aktif
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak ditemukan'
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak aktif, silahkan hubungi administrator'
            ], 401);
        }

        // Coba login
        if (Auth::attempt($credentials, $request->has('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Redirect berdasarkan role
            $redirectUrl = $this->getRedirectUrl($user->role);
            
            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'redirect_url' => $redirectUrl,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'department' => $user->department,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Email atau password salah'
        ], 401);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    private function getRedirectUrl($role): string
    {
        switch ($role) {
            case 'admin':
                return route('admin.dashboard');
            case 'hse_staff':
                return route('hse.dashboard');
            case 'employee':
                return route('employee.dashboard');
            default:
                return route('admin.dashboard');
        }
    }
}