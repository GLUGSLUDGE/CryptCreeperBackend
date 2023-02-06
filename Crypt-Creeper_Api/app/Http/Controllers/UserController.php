<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class UserController extends Controller
{
    public function create(Request $request) {
        $json = $request->getContent();
        $data = json_decode($json);

        $validator = Validator::make(json_decode($json, true),[
            'name' => 'required|min:3|max:10',
            'email' => 'required|email|max:30',
            'password' => ['required', 'min:4', 'max:8', Password::min(4)->mixedCase()],
            'faction_id' => 'required|digits_between:1,8|exists:factions,id',
            'profile_pic' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'Errores' => $validator->errors(),
            ], 422);
        };

        $user = new User();
        $user->name = $data->name;
        $user->email = $data->email;
        $user->password = Hash::make($data->password);
        $user->faction_id = $data->faction_id;
        $user->profile_pic = $data->profile_pic;

        $user->save();

        return response()->json($data, 201);
    }

    public function login(Request $request) {
        $json = $request->getContent();
        $data = json_decode($json);

        $validator = Validator::make(json_decode($json, true),[
            'name' => 'required',
            'password' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'Errores' => $validator->errors(),
            ], 422);
        } else {

            try {
                $user = User::where('name', 'like', $data->name)->firstOrFail();

                if(!Hash::check($data->password, $user->password)) {
                    return "La contraseña es incorrecta";
                } else {
                    $user->tokens()->delete();
                    $token = $user->createToken($user->name);
 
                    return ['token' => $token->plainTextToken];
                    return "Token creado correctamente";
                }

            } catch(\Exception $e) {
                return response([
                    "message" => "Ha ocurrido un error"
                ]);
            }
        }
        return response()->json($data, 201);
    }   

    public function logout(Request $request) {
        
    }

    // TODO: Funciones de editar usuario(nombre, contraseña y foto de perfil) y eliminar cuenta
}
