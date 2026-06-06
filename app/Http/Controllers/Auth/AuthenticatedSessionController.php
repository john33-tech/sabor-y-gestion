<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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
        $request->authenticate();
        $request->session()->regenerate();
        // Redirigir según el rol del usuario
        $user = Auth::user();
        
        switch ($user->role) {
            case 'admin':
                // El admin aterriza en el Dashboard Ejecutivo (analytics), no en el viejo.
                return redirect()->intended(route('admin.analytics'));
            case 'mesero':
                return redirect()->intended(route('dashboard.mesero'));
            case 'cocinero':
                return redirect()->intended(route('dashboard.cocinero'));
            case 'cajero':
                return redirect()->intended(route('dashboard.cajero'));
            case 'cliente':
                return redirect()->intended(route('dashboard.cliente'));
            default:
                return redirect()->intended(route('dashboard.cliente'));
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