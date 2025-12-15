<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // SQL Server User modeli
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{

    // Login formunu göster
    public function loginForm()
    {
        return view('auth.login');
    }

      // Login işlemi
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate(); // session fixation önlemi

            return redirect()->route('dashboard');

        }

        return back()->withErrors([
            'email' => 'E-posta veya şifre hatalı!',
        ]);
    }


    // Logout işlemi
    public function logout(Request $request)
    {
        $request->session()->forget(['user_id', 'user_name', 'user_email']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }


}
