<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\AuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
// Open Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes
Route::group(['middleware' => ['multi-auth', 'auth:sanctum']], function () {
Route::get('/user', [AuthController::class, 'user']);
Route::get('/profile', [AuthController::class, 'profile']);
Route::get('/logout', [AuthController::class, 'logout']);
  //roles
Route::get('/roles', [RoleController::class, 'getRoles']);
Route::post('/roles/update', [RoleController::class, 'updateRoles']);
Route::post('/roles/create', [RoleController::class, 'createRoles']);
Route::get('/permissions', [RoleController::class, 'getPermissions']);
Route::post('/roles/test', [RoleController::class, 'test']);
//usuarios
Route::get('/apps/users/list', [UserController::class, 'fetchUsers']);
Route::get('/users/roles/name', [UserController::class, 'fetchRolesName']);
Route::get('/users/roles/id', [UserController::class, 'fetchRolesId']);
Route::post('/users/create', [UserController::class, 'store']);
Route::post('/users/update', [UserController::class, 'update']);
Route::delete('/users/delete/{id}', [UserController::class, 'delete']);
//permisos
Route::get('/apps/permissions/data', [PermissionController::class, 'index']);
Route::post('/apps/permissions/create', [PermissionController::class, 'store']);
});