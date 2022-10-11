<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetAllUsersRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 *
 */
class ApiUserController extends Controller
{
    /**
     * @param GetAllUsersRequest $request
     * @return Builder[]|Collection
     */
    public function index(GetAllUsersRequest $request)
    {
        return User::with('roles')->orderBy('name', 'DESC')->get();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function selectRole(Request $request): JsonResponse
    {
        $role = $request->input('role');
        //Check if the user has the role.
        $userRoles = auth()->user()->roles;

        foreach ($userRoles as $Userrole) {
            if ($Userrole->id === $role) {
                //The user has the role, assign it
                session(['role' => $role]);
                return response()->json();
            }
        }
        return response()->json(['message' => 'No tienes asignado el rol seleccionado'], 403);
    }

    /**
     * @return mixed
     */
    public function getUserRoles()
    {
        return auth()->user()->roles;
    }


    /**
     * @param User $user
     * @param UpdateUserRoleRequest $request
     * @return JsonResponse
     */
    public function updateUserRoles(User $user, UpdateUserRoleRequest $request): JsonResponse
    {
        $roles = $request->input('roles');
        try {
            Role::findOrFail($roles);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Alguno de los roles proporcionados no se encuentra en la lista de roles'], 400);
        }
        $user->roles()->sync($roles);
        return response()->json(['message' => 'Los roles del usuario han sido actualizado exitosamente']);
    }
}
