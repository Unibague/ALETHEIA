<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        //return view('logout');
        return redirect('/');
    }

    public function handleAuthRedirect(): \Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();
        if ($user) {
            return redirect()->route('redirect');
        }
        return redirect()->route('login');
    }

    public function handleRoleRedirect()
    {
        $user = auth()->user();


        if ($user->role()->name == "Resultados Evaluación") {

            return redirect()->route('reports.showFaculty');
        }

        if($user->role()->name == "Jefe de Área de Servicio") {

            return redirect()->route('reports.showServiceArea');
        }


        if ($user->isAdmin()) {
            return redirect()->route('forms.index.view');
        }

        if($user->isUnitAdmin()){
            return redirect()->route('unities.index.view');
        }

        if ($user->role()->name == "estudiante") {

            Group::purifyGroups($user);
            return redirect()->route('tests.index.view');
        }




        if ($user->role()->name == "docente" || $user->role()->name == "jefe de profesor") {
            return redirect()->route('teachers.assessments.view');
        }




    }

    public function redirectGoogleLogin()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(): \Illuminate\Http\RedirectResponse
    {
        try {

            $googleUser = Socialite::driver('google')->user();

            $email = $googleUser->email;

            $indexOfAtSymbol = stripos($email, "@", 0);

            $unibagueString = strrpos($email, "unibague", $indexOfAtSymbol);

            if($unibagueString == false){
                return redirect()->route('login');
            }

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
            session(['role' => $user->roles[0]->id]);
            return redirect()->route('redirect');
        }
        return redirect()->route('pickRole');
    }

    public function pickRole()
    {
        return Inertia::render('Auth/PickRole');
    }


}
