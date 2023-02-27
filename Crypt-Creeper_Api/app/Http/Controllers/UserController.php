<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class UserController extends Controller
{
//  CREAR CUENTA
    public function create(Request $request) {
        $json = $request->getContent();
        $data = json_decode($json);

        $validator = Validator::make(json_decode($json, true),[
            'name' => 'required|min:3|max:10|unique:users,name',
            'email' => 'required|email|max:30|unique:users,email',
            'password' => ['required', 'min:4', 'max:8', Password::min(4)->mixedCase()],
            'faction_id' => 'required|digits_between:1,8|exists:factions,id',
            'profile_pic' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'Errors' => $validator->errors(),
            ], 422);
        } else {
            $user = new User();
            $user->name = $data->name;
            $user->email = $data->email;
            $user->password = Hash::make($data->password);
            $user->faction_id = $data->faction_id;
            // Recuperar la imagen en base64 desde la solicitud de entrada
            $image_data = $data->profile_pic;

            // Convertir la cadena base64 a un archivo temporal
            $temp_file = tempnam(sys_get_temp_dir(), 'img');
            file_put_contents($temp_file, base64_decode($image_data));

            // Crear un objeto UploadedFile a partir del archivo temporal
            $file = new UploadedFile($temp_file, $data->name.'.png', null, null, true);

            // Guardar los datos del archivo cargado en la base de datos
            $user->profile_pic = $file->store('public/images');

            try {
                $user->save();
                $token = $user->createToken($user->name);
            } catch(Exception $e) {
                return response([
                    'message' => 'An error has ocurred trying to create an user'
                ]);
            }
            
            return response([
                'Token' => $token->plainTextToken,
                'message' => 'Token created successfully',
                'User' => $data
            ], 201);
        }
    }

//  INICIAR SESIÓN
    public function login(Request $request) {
        $json = $request->getContent();
        $data = json_decode($json);

        $validator = Validator::make(json_decode($json, true),[
            'name' => 'required',
            'password' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'Errors' => $validator->errors(),
            ], 422);
        } else {
            try {
                
                $user = User::where('name', 'like', $data->name)->firstOrFail();

                if(!Hash::check($data->password, $user->password)) {
                    return response([
                        'message' => 'The user or the password is incorrrect'
                    ]);
                } else {
                    $user->tokens()->delete();
                    $token = $user->createToken($user->name);
                    
                    return response([
                        'Token' => $token->plainTextToken,
                        'message' => 'Token created successfully'
                    ]);
                }

            } catch(\Exception $e) {
                return response([
                    "message" => "The user or the password are incorrect"
                ]);
            }
        }
        return response()->json($data, 201);
    }   

//  CERRAR SESION
    public function logout(Request $request) {
        
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'closed session']);
        
    }
   
//  CAMBIAR NOMBRE
    public function changeName(Request $request)
    {
        $json = $request->getContent();
        $data = json_decode($json);

        $validator = Validator::make(json_decode($json, true),[
            'name'=> 'required|min:3|max:10',
        ]);

        if ($validator ->fails()){
            return response()->json(['Erros' => $validator->errors()],400);
        } 

        try
        {
            $user = $request->user();
            $user->name = $request->input('name');
            $user ->save();
        }
        catch(\Exception $e) 
        {
            return response([
                "message" => "An error has occurred"
            ]);
        }

       return response()->json(['message' => 'Name updated successfully']);
    }

//  CAMBIAR CONTRSEÑA
    public function changePassword(Request $request)
    {
        $json = $request->getContent();
        $data = json_decode($json);

        $validator = Validator::make(json_decode($json, true),[
            'password' => 'required',
            'new_password' => ['required', 'min:4', 'max:8', Password::min(4)->mixedCase()],
            'repit_new_password' => 'required'
        ]);

        if ($validator ->fails())
        {
            return response()->json(['Erros' => $validator ->errors()],400);
        }

        try
        {
            $user = $request->user();
            if(!Hash::check($data->password, $user->password)) {
                return response([
                    "message" =>"The password is incorrect"
                ]);
            }
            else
            {
                if($data->new_password !== $data->repit_new_password )
                {
                    return response()->json(['Passwords do not match'],401);

                }
                else
                {
                    $user->password = Hash::make($request->input('new_password'));
                    $user->save();
                }
            }
        }
        catch(\Exception $e) 
        {
            return response([
                "message" => "An error has occurred"
            ]);
        }

        return response()->json(['message' => 'Password changed correctly']);
    }

//  CAMBIAR FOTO
    public function changePhoto(Request $request)
    {
        $json = $request->getContent();
        $data = json_decode($json);

        $validator = Validator::make(json_decode($json, true),[
            'profile_pic' => 'required',
        ]);
        if ($validator ->fails())
        {
            return response()->json(['Erros' => $validator ->errors()],400);
        }
        try
        {
            $user = $request->user();
            $user ->profile_pic = $request->input('profile_pic');
            $user->save();
        }
        catch(\Exception $e)
        {
            return response([
                "message" => "An error has occurred"
            ]);
        }

        return response()->json(['message' => 'Photo changed successfully']);
    }

//  BORRAR CUENTA
    public function deleteUser(Request $request)
    {
        $json = $request->getContent();
        $data = json_decode($json);
        $validator = Validator::make(json_decode($json, true),[
            'password' => 'required',
        ]);

        if ($validator ->fails())
        {
            return response()->json(['Erros' => $validator ->errors()],400);
        }

        try
        {
            $user = $request->user();
            if(!Hash::check($data->password, $user->password))
            {
                return response([
                    "message" => "The password is incorrect"
                ]);
            }
            else
            {
                $user->delete();
            }
        }
        catch(\Exception $e)
        {
            return response([
                "message" => "An error has occurred"
            ]);
        }

        return response()->json(['message' => 'Photo changed correctly ']);
    }

}
