<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    //

    public function handleRoleRedirect()
    {

    }

    public function redirectGoogleLogin()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(): \Illuminate\Http\RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();

        } catch (\Exception $e) {
            return redirect()->route('login');
        }
        $user = User::where('email', $googleUser->email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'password' => Hash::make('automatic_generated_password'),
            ]);

            //Assign the default role (student)
            $role = Role::where('name', 'estudiante')->first();
            Role::assignRole($user->id, $role->id);
            session(['role' => $role->id]);
        }
        //log the user in
        Auth::login($user);

        //Check if the user has more than one role
        if ($user->hasOneRole()) {
            return redirect()->route('landing');
        }
        return redirect()->route('pickRole');
    }

    public function pickRole()
    {
        $user = Auth::user();
        $roles = $user->roles;

        return Inertia::render('Auth/PickRole');
    }


}
