<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\JsonResponse;
use App\Models\User;

class RoleController extends Controller
{
    public function getRoles(): JsonResponse
    {
        // Obtener los roles con sus permisos
        $roles = Role::with('permissions')->get();
    
        // Obtener los usuarios que tienen estos roles desde la tabla model_has_roles
        $users = User::whereHas('roles')->get(); // Obtener los usuarios que tienen roles
    
        // Añadir los usuarios a los roles
        $rolesWithUsers = $roles->map(function ($role) use ($users) {
            // Filtrar los usuarios que tienen el rol actual a través de la tabla model_has_roles
            $role->users = $users->filter(function ($user) use ($role) {
                return $user->roles->contains($role);
            })->values(); // Reseteamos los índices del array
            return $role;
        });
    
        // Obtener todos los permisos disponibles
        $permissions = Permission::all();
    
        // Retornar los roles con usuarios y permisos
        return response()->json([
            'roles' => $rolesWithUsers,
            'permisos' => $permissions,
        ]);
    }
    public function getPermissions(): JsonResponse
    {
        $permissions = Permission::all(); // Asegúrate de que estás obteniendo todos los permisos
        return response()->json($permissions);
    }
    
    public function updateRoles(Request $request)
    {
        // Validar la estructura del request
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
            'name' => 'required|string|max:255',
            'role_id' => 'nullable|exists:roles,id', // role_id es opcional
        ]);
    
        // Obtener los permisos del request
        $permissions = $validated['permissions'];
    
        // Definir el guard que quieres usar
        $guard = 'web'; // O el guard que estés usando, por ejemplo 'api'
    
        // Comprobar si role_id está definido antes de usarlo
        if (isset($validated['role_id'])) {
            // Buscar el rol por ID y guard
            $role = Role::findById($validated['role_id'], $guard);
            
            if ($role) {
                // Actualizar el nombre del rol
                $role->name = $validated['name'];
                $role->save(); // Guardar los cambios
    
                // Sincronizar permisos con el rol
                $role->syncPermissions($permissions);
            } else {
                return response()->json(['message' => 'Role not found'], 404);
            }
        } else {
            // Crear un nuevo rol si no se proporciona role_id
            $role = Role::create(['name' => $validated['name'], 'guard_name' => $guard]);
    
            // Asignar permisos al nuevo rol
            $role->syncPermissions($permissions);
        }
    
        return response()->json(['message' => 'Permissions updated successfully'], 200);
    }
    public function test(Request $request)
    {
      var_dump($request->all());
    }
}
