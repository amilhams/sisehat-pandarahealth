<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Owner;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Mendaftarkan Owner baru
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:owners',
            'gender' => 'required|in:laki-laki,perempuan',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $owner = Owner::create([
            'name' => $request->name,
            'email' => $request->email,
            'gender' => $request->gender,
            'password' => Hash::make($request->password),
        ]);

        // Opsional: Langsung login setelah register
        Auth::guard('owner')->login($owner);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Registrasi berhasil',
                'data' => $owner
            ], 201);
        }

        return redirect()->route('beranda');
    }

    /**
     * Login untuk Owner
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');

        if (Auth::guard('owner')->attempt($credentials)) {
            $request->session()->regenerate();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Login berhasil',
                    'data' => Auth::guard('owner')->user()
                ]);
            }
            
            return redirect()->route('beranda');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Email atau password salah'
            ], 401);
        }

        return redirect()->back()->with('error', 'Email atau password salah')->withInput($request->except('password'));
    }

    /**
     * Logout untuk Owner
     */
    public function logout(Request $request)
    {
        Auth::guard('owner')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Logout berhasil'
            ]);
        }

        return redirect()->route('login');
    }

    /**
     * Cek user yang sedang login
     */
    public function me(Request $request)
    {
        if (Auth::guard('owner')->check()) {
            return response()->json([
                'message' => 'Mendapatkan data owner',
                'data' => Auth::guard('owner')->user()
            ]);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
