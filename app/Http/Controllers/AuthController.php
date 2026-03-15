<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
        {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $user = User::where('email', $request->email)->first();

            if(!$user || !Hash::check($request->password, $user->password)){
                    return response()->json([
                        'message'=>'Credenciais inválidas'
                    ], 401);
            };

            $token = $user->createToken('auth_token')->plainTextToken;

                    return response()->json([
            'message' => 'Login realizado com sucesso'
        ])->cookie(
            'auth_token',
            $token,
            60 * 24, // 1 dia
            '/',
            null,
            false,
            true
        );
        
        }
}
