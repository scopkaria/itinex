<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Itinerary\Itinerary;
use App\Models\MasterData\Destination;
use App\Models\MasterData\Hotel;
use App\Models\MasterData\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class WebAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!Auth::user()->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        $request->session()->regenerate();

        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function dashboard()
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        if ($user->isSuperAdmin()) {
            $stats = [
                'destinations' => Destination::count(),
                'hotels' => Hotel::count(),
                'vehicles' => Vehicle::count(),
                'itineraries' => Itinerary::count(),
            ];
            $companies = Company::withCount('users')->orderBy('name')->get();
            $users = User::with('company')->orderBy('name')->get();
        } else {
            $stats = [
                'destinations' => Destination::where('company_id', $companyId)->count(),
                'hotels' => Hotel::where('company_id', $companyId)->count(),
                'vehicles' => Vehicle::where('company_id', $companyId)->count(),
                'itineraries' => Itinerary::where('company_id', $companyId)->count(),
            ];
            $companies = collect();
            $users = collect();
        }

        return view('dashboard', compact('stats', 'companies', 'users'));
    }
}
