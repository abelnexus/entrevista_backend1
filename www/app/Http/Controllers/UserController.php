<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
class UserController extends Controller
{ // Asegúrate de importar el modelo de Role si es necesario.

    public function fetchUsers(Request $request)
    {
        // Captura los parámetros de la solicitud
        $q = $request->input('q', '');
        $role = $request->input('role', null);
        $plan = $request->input('plan', null);
        $status = $request->input('status', null);
        $options = $request->input('options', []);
    
        // Configura la paginación
        $itemsPerPage = $options['itemsPerPage'] ?? 10;
        $page = $options['page'] ?? 1;
    
        // Consulta a la base de datos
        $query = User::query();
    
        // Filtra por nombre, email, rol, plan y estado
        if ($q) {
            $query->where(function ($query) use ($q) {
                $query->where('name', 'like', '%' . $q . '%')
                      ->orWhere('email', 'like', '%' . $q . '%');
            });
        }
        if ($role) {
            $query->whereHas('roles', function ($query) use ($role) {
                $query->where('name', $role); // Asumiendo que el campo de rol se llama 'name'
            });
        }
        if ($plan) {
            $query->where('currentPlan', $plan);
        }
        if ($status) {
            $query->where('status', $status);
        }
    
        // Obtiene los usuarios filtrados y paginados
        $totalUsers = $query->count();
        $totalPages = ceil($totalUsers / $itemsPerPage);
        $users = $query->paginate($itemsPerPage, ['*'], 'page', $page);
    
        // Incluye los roles en el resultado
        $usersWithRoles = $users->map(function ($user) {
            // Obtener el primer rol (que es un objeto) en lugar de solo el nombre
            $role = $user->getRoleNames()->first(); // Nombre del rol
            $roleId = null;
        
            // Si el usuario tiene un rol, busca el ID del rol
            if ($role) {
                $roleModel = Role::where('name', $role)->first(); // Obtener el modelo de rol
                $roleId = $roleModel ? $roleModel->id : null; // Obtener el ID del rol
            }
        
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => [
                    'id' => $roleId, // ID del rol
                    'name' => $role, // Nombre del rol
                ],
                'cellphone' => $user->cellphone,
                'status' => $user->status,
            ];
        });
    
        return response()->json([
            'users' => $usersWithRoles,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers,
            'page' => $users->currentPage(),
        ]);
    }
    public function fetchRolesName(Request $request)
    {
        // Obtiene todos los roles de la base de datos
        $roles = Role::all();

        // Mapea los roles a un formato más sencillo
        $formattedRoles = $roles->map(function ($role) {
            return [
                'title' => ucfirst($role->name), // Capitaliza el primer carácter
                'value' => $role->name,
            ];
        });

        return response()->json($formattedRoles);
    }
    public function fetchRolesiD(Request $request)
    {
        // Obtiene todos los roles de la base de datos
        $roles = Role::all();

        // Mapea los roles a un formato más sencillo
        $formattedRoles = $roles->map(function ($role) {
            return [
                'title' => ucfirst($role->name), // Capitaliza el primer carácter
                'value' => $role->id,
            ];
        });

        return response()->json($formattedRoles);
    }
    public function store(Request $request)
    {
        // Validar los datos recibidos
        $validatedData = $request->validate([
            'name'     => 'required|string|max:255',
            'email'        => 'required|string|email|max:255|unique:users',
            'password'     => 'required|string|min:6',
            'role'         => 'required|int|exists:roles,id',
            'cellphone'      => 'nullable|string|max:255',
            'status'       => 'required|in:activo,inactivo,pendiente',
        ]);

        // Crear el usuario con los datos validados
        $user = User::create([
            'name'     => $validatedData['name'],
            'email'    => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'cellphone'  => $validatedData['cellphone'] ?? '',
            'status'   => $validatedData['status'],
        ]);

        // Asigna el rol usando Spatie Permission
        $user->assignRole($validatedData['role']);

        return response()->json([
            'message' => 'Usuario creado con éxito',
            'user'    => $user,
        ], 201);
    }
    public function update(Request $request)
    {
        // Buscar al usuario por el ID proporcionado
        $user = User::findOrFail($request->id);
    
        // Validar los datos recibidos, ignorando el email del usuario actual
        $validatedData = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6', // El password es opcional en la actualización
            'role'     => 'required|int|exists:roles,id',
            'cellphone'=> 'nullable|string|max:255',
            'status'   => 'required|in:activo,inactivo,pendiente',
        ]);
    
        // Actualizar los datos del usuario
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->cellphone = $validatedData['cellphone'] ?? '';
        $user->status = $validatedData['status'];
    
        // Si se proporciona una nueva contraseña, actualizarla
        if (!empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }
    
        // Guardar los cambios en el usuario
        $user->save();
    
        // Actualizar el rol del usuario usando Spatie Permission
        $user->syncRoles($validatedData['role']);
    
        return response()->json([
            'message' => 'Usuario actualizado con éxito',
            'user'    => $user,
        ], 200);
    }
    public function delete($id)
    {
        // Busca el usuario por ID
        $user = User::find($id);

        // Verifica si el usuario existe
        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado.'
            ], 404);
        }

        // Elimina el usuario
        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado con éxito.'
        ]);
    }
}
