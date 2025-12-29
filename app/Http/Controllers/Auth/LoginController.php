<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Redirect user berdasarkan role setelah login sukses.
     */
protected function authenticated(Request $request, $user)
{
    // Jika login dari halaman SMI
    if ($request->has('smi_login')) {
        return redirect('/home');
    }

    // Login default berdasarkan role
    switch (strtolower($user->role)) {

        case 'administrator':
            return redirect('/admin/penjualan');

        case 'marketing':
            return redirect('/marketing');

        case 'manager':
            return redirect('/manager');

        case 'hr': // ✅ Tambahan role HR
        case 'human_resource': // opsional jika nama role berbeda
            return redirect('/hr'); // sesuaikan dengan route dashboard HR kamu
        
        case 'advertising':
            return redirect('/advertising');
        
        default:
            return redirect('/home');
    }
}



    /**
     * Override: jika login gagal tampilkan SweetAlert.
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        return back()
            ->withInput($request->only('email', 'remember'))
            ->with('loginError', 'Email atau password yang Anda masukkan salah!');
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
