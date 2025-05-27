<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class UserRolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Definir grupos de permisos y acciones
        // Lista de permisos por módulo
        $permissions = [
            'usuarios.vista',
            'usuarios.editar',
            'usuarios.crear',
            'usuarios.eliminar',
            'roles.vista',
            'roles.editar',
            'roles.crear',
            'roles.eliminar',
            'permisos.vista',
            'permisos.editar',
            'permisos.crear',
            'permisos.eliminar',
        ];

        // Crear permisos con el guard_name "web"
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Crear rol
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']); // Cambia esto si utilizas otro guard

        // Asignar todos los permisos al rol
        $role->givePermissionTo(Permission::all());

        // Crear usuario
        $user = User::create([
            'name' => 'Fernando Abel Gonzales',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('123456'), // Cambia la contraseña si lo deseas
            'cellphone' => '936765770',
            'status' => 'activo'
        ]);

        // Asignar rol al usuario
        $user->assignRole(roles: $role);
    }
}
