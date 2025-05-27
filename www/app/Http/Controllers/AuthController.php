<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required|string',
            'login_as' => 'required|string'
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->plainTextToken;

        // Nombre de cookie distinto por tipo
        $cookieName = $request->input('login_as') . '_token';

        return response()->json([
            'message' => 'Login exitoso',
        ])->cookie($cookieName, $token, 60 * 24, '/', null, false, false);
    }
    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required',
    //         'password' => 'required|string',
    //         'remember_me' => 'boolean'
    //     ]);


    //     $credentials = request(['email', 'password']);
    //     if (!Auth::attempt($credentials)) {
    //         return response()->json([
    //             'message' => 'Email o contraseña incorrectos'
    //         ], 202);
    //     }

    //     $user = $request->user();
    //     $tokenResult = $user->createToken('Personal Access Token');
    //     $token = $tokenResult->plainTextToken;
    //     return response()->json([
    //         'accessToken' => $token,
    //         'token_type' => 'Bearer',
    //         'id' => $request->user()->id
    //     ]);
    // }

    public function user(Request $request)
    {
        // return new UserResource($request->user());
        $user = $request->user();
        // $permissions = $user->getAllPermissions()->pluck('name');
        $permissions = $user->getAllPermissions()->pluck('name');
        $role = $user->getRoleNames();
        $ability = new \stdClass();
        $total = count($permissions);
        $i = 0;
        foreach ($permissions as $item) {
            $ability->ability[$i] = ['action' => $item, 'subject' => 'ACL'];
            $i++;
        }
        $homeAbility = $total + 1;
        $ability->ability[$total] = ['action' => 'read', 'subject' => 'Auth'];
        $ability->ability[$homeAbility] = ['action' => 'read', 'subject' => 'Home'];
        $user->ability = $ability->ability;
        $user->role = $role[0];
        return response()->json($user);
    }
    public function logout(Request $request)
    {
        // Obtener el usuario autenticado
        $user = Auth::user();
    
        if ($user) {
            // Revocar todos los tokens de acceso del usuario
            $user->tokens()->delete();
    
            // Responder con un mensaje de éxito
            return response()->json(['message' => 'Logout exitoso'], 200);
        }
    
        return response()->json(['message' => 'No hay un usuario autenticado'], 401);
    }
}