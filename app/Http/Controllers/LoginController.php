<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // For demo purposes, create the user if they don't exist
            $user = User::create([
                'name' => explode('@', $request->email)[0],
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            Log::info('Created new user during login', ['user_id' => $user->id, 'email' => $user->email]);
        }

        // Update last_login_at timestamp
        $user->update(['last_login_at' => now()]);

        Log::info('User logged in', ['user_id' => $user->id, 'email' => $user->email, 'last_login_at' => $user->last_login_at]);

        return redirect('/')->with('success', 'Logged in successfully! Last login: ' . now()->format('Y-m-d H:i:s'));
    }

    /**
     * Show the demo users list.
     */
    public function users()
    {
        $users = User::orderBy('id')->get();
        return view('users', compact('users'));
    }

    /**
     * Manually update a user's last_login_at for testing.
     */
    public function updateLastLogin(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $days = $request->input('days', 7);

        $user->update([
            'last_login_at' => now()->subDays($days),
            'reminder_sent_at' => null, // Reset reminder to test again
        ]);

        return redirect('/users')->with('success', "Updated last_login_at for {$user->email} to {$days} days ago.");
    }
}
