<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        $user = User::where('username', $validated['username'])->first();

        if ($user) {
            if ($user->is_active === false) {
                return back()->withErrors(['username' => 'Your account has been deactivated.']);
            }

            if (Auth::attempt([
                'username' => $validated['username'],
                'password' => $validated['password'],
            ], $remember)) {
                $request->session()->regenerate();
                $user->update(['last_login_at' => now()]);
                app(ActivityLoggerService::class)->logLogin($user->username);

                return $this->redirectUser($user);
            }

            if ($this->checkOldPassword($user, $validated['password'])) {
                $user->password = Hash::make($validated['password']);
                $user->old_password = null;
                $user->last_login_at = now();
                $user->save();

                Auth::login($user, $remember);
                $request->session()->regenerate();
                app(ActivityLoggerService::class)->logLogin($user->username);

                return $this->redirectUser($user);
            }
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    public function destroy(Request $request): RedirectResponse
    {
        app(ActivityLoggerService::class)->logLogout(Auth::user()?->username ?? 'unknown');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function checkOldPassword(User $user, string $password): bool
    {
        if (empty($user->old_password)) {
            return false;
        }

        $expected = strtoupper(sha1(strtolower($user->username) . $password));

        return strtoupper($user->old_password) === $expected;
    }

    private function redirectUser(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('dashboard'));
    }
}
