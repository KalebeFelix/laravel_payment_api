<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
        {
            $user = User::where('email', $request->email)->first();

            if(!$user || !Hash::check($request->password, $user->password)){
                    return response()->json([
                        'message'=>'Credenciais inválidas'
                    ], 401);
            };

            $token = $user->createToken('auth_token')->plainTextToken;

            // Retorna no body JSON
            return response()->json([
                'message' => 'Login realizado com sucesso',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ], 200);
            
            }
}
