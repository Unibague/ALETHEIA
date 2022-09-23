<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetAllUsersRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiUserController extends Controller
{
    public function index(GetAllUsersRequest $request)
    {
        return User::with('role')->get();
    }

    public function selectRole(Request $request)
    {
        $role = $request->input('role');
        //Check if the user has the role.
        $userRoles = auth()->user()->roles;

        foreach ($userRoles as $Userrole) {
            if ($Userrole->id === $role) {
                session(['role' => $role]);
                return response()->json();
            }
        }
        return response()->json(['message' => 'No tienes asignado el rol seleccionado'], 403);
    }

    public function getUserRoles()
    {
        return auth()->user()->roles;
    }


    public function updateUserRole(User $user, UpdateUserRoleRequest $request)
    {
        $user->role_id = $request->roleId;
        $user->save();
        return response()->json(['message' => 'El rol del usuario ha sido actualizado exitosamente']);
    }
}
