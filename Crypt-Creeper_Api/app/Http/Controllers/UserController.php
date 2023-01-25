<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class UserController extends Controller
{
    public function create(Request $request) {
        $response = [
            "status" => "",
            "code" => 0,
            "data" => []
        ];

        $json = $request->getContent();
        $data = json_decode($json, true);

        $validator = Validator::make($data,[
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
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = $request->password;
        $user->faction_id = $request->faction_id;
        $user->profile_pic = $request->profile_pic;

        $user->save();

        /* try {
            $user->save();
        } catch (\Exception $e) {
            $response['status'] = "Error al guardar al usuario";
        } */

        return response()->json($data, 201);
    }

    public function get_users() {

    }

    public function update_users() {
        
    }
}
