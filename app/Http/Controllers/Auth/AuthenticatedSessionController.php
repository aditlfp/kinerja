<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $cekUser = User::where('name', $request->name)->where('status_id', 6)->first();

        if($cekUser) {
            toastr()->error('Akun Anda Belum Di Verifikasi');
            return redirect()->back();
        }

        $request->authenticate();
        $this_auth = Auth::user();
        if (!$request->session()->has('is_modal')) {
            $request->session()->put('is_modal', true);
        }

        $request->session()->regenerate();

        if($this_auth->role_id == 2) {
            // dd($data);
            return redirect()->intended(RouteServiceProvider::ADMIN);
        }else {
            return redirect()->intended(RouteServiceProvider::HOME);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
