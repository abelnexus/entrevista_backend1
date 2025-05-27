<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        // Obtener los permisos desde el modelo Permission
        $permissions = Permission::all()->map(function ($permission) {
            return [
                'name' => $permission->name,
                'createdDate' => $permission->created_at->format('Y-m-d'), // Formatear la fecha
                'assignedTo' => $permission->roles->pluck('name')->toArray(), // Obtener los roles asignados
            ];
        });
    
        // Obtener los parámetros de la solicitud
        $params = $request->query();
        $q = $params['q'] ?? '';
        $options = $params['options'] ?? [];
        $sortBy = $options['sortBy'] ?? '';
        $page = $options['page'] ?? 1;
        $itemsPerPage = $options['itemsPerPage'] ?? 10;
    
        // Filtrar los permisos
        $filteredData = $permissions->filter(function ($permission) use ($q) {
            return stripos($permission['name'], $q) !== false ||
                   stripos($permission['createdDate'], $q) !== false ||
                   array_filter($permission['assignedTo'], function ($assignedTo) use ($q) {
                       return stripos($assignedTo, $q) === 0;
                   });
        });
    
        // Ordenar los permisos
        if (!empty($sortBy) && $sortBy['key'] === 'name') {
            $filteredData = $filteredData->sortBy(function ($permission) use ($sortBy) {
                return $permission['name'];
            });
    
            if ($sortBy['order'] === 'desc') {
                $filteredData = $filteredData->reverse();
            }
        }
    
        // Total de permisos
        $totalPermissions = $filteredData->count();
        $totalPages = ceil($totalPermissions / $itemsPerPage);
    
        // Paginación
        $pagedData = $filteredData->slice(($page - 1) * $itemsPerPage, $itemsPerPage)->values();
    
        return response()->json([
            'permissions' => $pagedData,
            'totalPermissions' => $totalPermissions,
            'totalPages' => $totalPages,
        ]);
    }
    public function store(Request $request)
    {
        // Validar que el campo 'permission_name' esté presente
        $request->validate([
            'permission_name' => 'required|string|max:255',
        ]);
    
        $basePermission = $request->input('permission_name');
    
        // Crear los 4 permisos automáticamente
        $permissions = [
            "{$basePermission}.vista",
            "{$basePermission}.crear",
            "{$basePermission}.editar",
            "{$basePermission}.eliminar",
        ];
    
        // Verificar si los permisos ya existen
        $existingPermissions = Permission::whereIn('name', $permissions)->pluck('name')->toArray();
    
        if (!empty($existingPermissions)) {
            // Si hay permisos existentes, devolver un mensaje con la lista de permisos duplicados
            return response()->json([
                'message' => 'Algunos permisos ya existen.',
                'existing_permissions' => $existingPermissions
            ], 409); // Código de respuesta 409: Conflicto
        }
    
        // Crear los permisos que no existen
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission,'guard_name' => "web"]);
        }
    
        return response()->json([
            'message' => 'Permisos creados exitosamente',
            'permissions' => $permissions
        ], 201);
    }
}
