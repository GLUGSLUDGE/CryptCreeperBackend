<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Faction;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;

class UserController extends Controller
{
//  CREAR CUENTA
    /**
     * Create the specified resource.
     * Creates a new user.
     * @param string  $name
     * @param string $email
     * @param string $password
     * @param string profile_pic
     * @param int faction_id
     * @return \Illuminate\Http\Response
     * 
     * @OA\Put(
     *     path="/api/user/create",
     *     tags={"user"},
     *     summary="Creates a new user.",
     *     @OA\Parameter(
     *         description="Name of the user.",
     *         in="path",
     *         name="name",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="string", value="abbytorade", summary="Name for the new user")
     *     ),
     *     @OA\Parameter(
     *         description="Email of the user.",
     *         in="path",
     *         name="email",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="string", value="abbytorade@icloud.com", summary="The email for the new user")
     *     ),
     *      @OA\Parameter(
     *         description="Password for the new user",
     *         in="path",
     *         name="password",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="string", value="Ud345678", summary="The password for the user")
     *     ),
     *      @OA\Parameter(
     *         description="ID of the faction to join.",
     *         in="path",
     *         name="faction_id",
     *         required=true,
     *         @OA\Schema(type="int"),
     *         @OA\Examples(example="int", value="6", summary="The id for the faction to join")
     *     ),
     *     @OA\Parameter(
     *         description="Profile picture for the new user",
     *         in="path",
     *         name="profile_pic",
     *         required=true,
     *         @OA\Schema(type="int"),
     *         @OA\Examples(example="string", value="(base64 picture)", summary="The picture in base64")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User was created."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Info entered was invalid."
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="An error occurred."
     *     )
     * ) 
     */
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
            $url = Storage::url($data->name.'.png');
            $finalUrl = 'http://127.0.0.1:8000'.$url;
            // Guardar los datos del archivo cargado en la base de datos
            $file->storeAs('public', $user->name.'.png');
            $user->profile_pic = $finalUrl;

            
            try {
                $user->save();
                $token = $user->createToken($user->name);
                $faction = DB::table('factions')
                    ->join('users', 'factions.id', '=', 'users.faction_id')
                    ->where('users.id', '=', $user->id,)
                    ->select('factions.name')
                    ->first()
                    ->name;
                Mail::to($user->email)->send(new WelcomeMail($user->name, $faction));
            } catch(Exception $e) {
                return response([
                    'message' => 'An error has ocurred trying to create an user'
                ], 500);
            }
            
            return response([
                'Token' => $token->plainTextToken,
                'message' => 'Token created successfully',
                'User' => $user
            ], 201);
        }
    }

//  INICIAR SESIÓN
    /**
     * Create the specified resource.
     * Creates a new token for the user.
     * @param string  $name
     * @param string $password
     * @return \Illuminate\Http\Response
     * 
     * @OA\Post(
     *     path="/api/user/login",
     *     tags={"user"},
     *     summary="Creates a new token for the user.",
     *     @OA\Parameter(
     *         description="Name of the user.",
     *         in="path",
     *         name="name",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="string", value="abbytorade", summary="Name of the user")
     *     ),
     *     @OA\Parameter(
     *         description="Password of the user",
     *         in="path",
     *         name="password",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="string", value="Ud345678", summary="The password for the user")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Token was created."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Info entered was invalid."
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="An error occurred."
     *     )
     * ) 
     */
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
                    ], 422);
                } else {
                    $user->tokens()->delete();
                    $token = $user->createToken($user->name);
                    
                    return response([
                        'Token' => $token->plainTextToken,
                        'message' => 'Token created successfully',
                        'User' => $user
                    ], 201);
                }

            } catch(\Exception $e) {
                return response([
                    "message" => "There was an error with the server"
                ], 500);
            }
        }
        return response()->json($data, 201);
    }  
//  DATOS DE USUARIO
    /**
     * Retrieves the specified resource.
     * Gets all the data from the logged in user.
 
     * @return \Illuminate\Http\Response
     * 
     * @OA\Get(
     *     path="/api/user/getUserData",
     *     tags={"user"},
     *     summary="Retrieves all the data from the logged in user.",
     *     @OA\Response(
     *         response=200,
     *         description="All data from the user"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="An error occurred."
     *     )
     * ) 
     */
    public function getUserData (Request $request){
        try{
            $user = $request->user();
            return response()->json([
                'name'=> $user->name,
                'profile_pic' =>$user-> profile_pic,
                'faction_id' => $user-> faction_id
            ], 200);
        }catch(Exception $e){
            return response()->json([
                'message'=> "there was an error with the server",
            ], 500);
        }
    }

// USER TOP SCORES
    /**
     * Retrieves the specified resource.
     * Gets the best 8 scores from the user.
 
     * @return \Illuminate\Http\Response
     * 
     * @OA\Get(
     *     path="/api/user/getTop8",
     *     tags={"user"},
     *     summary="Retrieves the top 8 plays from the user.",
     *     @OA\Response(
     *         response=200,
     *         description="List of plays from the user"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="An error occurred."
     *     )
     * ) 
     */
    public function getTop8(Request $request) {
        try{
            $topScores = DB::table('plays')
            ->select('user_id', 'points')
            ->where('user_id','=', $request->user()->id)
            ->orderByDesc('points')
            ->limit(8)
            ->get();

        return response()->json([
            'score '=> $topScores]
        , 200);
        } catch (Exception $e){
            return response()->json([
                "message"=>"there was an error with the server"
            ], 500);
        }
        
    }

//  CERRAR SESION
    /**
     * Destroys the specified resource.
     * Logs out an user by deleting the token used.
 
     * @return \Illuminate\Http\Response
     * 
     * @OA\Post(
     *     path="/api/user/logout",
     *     tags={"user"},
     *     summary="Logs out the user by deleting the token used.",
     *     @OA\Response(
     *         response=200,
     *         description="The token was deleted"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="An error occurred."
     *     )
     * ) 
     */
    public function logout(Request $request) {
        try{
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'closed session'], 200);
        } catch(Exception $e){
            response()->json(['message' => 'There was an error with the server'], 500);
        }
        
        
    }
   
//  CAMBIAR NOMBRE
    /**
     * Changes the specified resource.
     * Changes the username of the logged in user.
     * @param string $name
     * @return \Illuminate\Http\Response
     * 
     * @OA\Post(
     *     path="/api/user/change-name",
     *     tags={"user"},
     *     summary="Changes the name of the user.",
     *      @OA\Parameter(
     *         description="Name of the user.",
     *         in="path",
     *         name="name",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="string", value="abbytorade", summary="New name for the user")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Name was changed successfully "
     *     ),
     *      @OA\Response(
     *         response=400,
     *         description="Name is not valid"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="An error occurred."
     *     )
     * ) 
     */
    public function changeName(Request $request){
        $json = $request->getContent();
        $data = json_decode($json);

        $validator = Validator::make(json_decode($json, true),[
            'name'=> 'required|min:3|max:10',
        ]);

        if ($validator ->fails()){
            return response()->json(['Errors' => $validator->errors()],400);
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
            ],500);
        }
       return response()->json(['message' => 'Name updated successfully'],200);
    }

//  CAMBIAR CONTRSEÑA
    /**
     * Changes the specified resource.
     * Changes the password of the logged in user.
     * @param string $password
     * @param string $new_password
     * @param string $repeated_password
     * @return \Illuminate\Http\Response
     * 
     * @OA\Post(
     *     path="/api/user/change-password",
     *     tags={"user"},
     *     summary="Changes the name of the user.",
     *      @OA\Parameter(
     *         description="Password of the user",
     *         in="path",
     *         name="password",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="string", value="Ud345678", summary="Old password")
     *     ),
     *     @OA\Parameter(
     *         description="New pasword for the user",
     *         in="path",
     *         name="new_password",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="string", value="876543dU", summary="New password")
     *     ),
     *     @OA\Parameter(
     *         description="Repeat new pasword for the user",
     *         in="path",
     *         name="repeat_password",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="string", value="876543dU", summary="New password (repeated)")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Name was changed successfully "
     *     ),
     *      @OA\Response(
     *         response=400,
     *         description="Password is not valid"
     *     ),
     *      @OA\Response(
     *         response=401,
     *         description="Passwords do not match"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="An error occurred."
     *     )
     * ) 
     */
    public function changePassword(Request $request){
        $json = $request->getContent();
        $data = json_decode($json);

        $validator = Validator::make(json_decode($json, true),[
            'password' => 'required',
            'new_password' => ['required', 'min:4', 'max:8', Password::min(4)->mixedCase()],
            'repit_new_password' => 'required'
        ]);

        if ($validator ->fails())
        {
            return response()->json(['Errors' => $validator ->errors()],400);
        }

        try
        {
            $user = $request->user();
            if(!Hash::check($data->password, $user->password)) {
                return response()->json([
                    "message" =>"The password is incorrect"
                ]);
            }
            else
            {
                if($data->new_password !== $data->repit_new_password )
                {
                    return response()->json(["message"=>"Passwords do not match"],401);

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
            ], 500);
        }

        return response()->json(['message' => 'Password changed correctly'], 200);
    }

//  CAMBIAR FOTO
    /**
     * Changes the specified resource.
     * Changes the profile picture of the logged in user.
     * @param string $photo
     * @return \Illuminate\Http\Response
     * 
     * @OA\Post(
     *     path="/api/user/change-photo",
     *     tags={"user"},
     *     summary="Changes the photo of the user.",
     *      @OA\Parameter(
     *         description="Photo of the user",
     *         in="path",
     *         name="photo",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="string", value="(base64image)", summary="Image in base64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Photo was changed successfully "
     *     ),
     *      @OA\Response(
     *         response=400,
     *         description="Photo is not valid"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="An error occurred."
     *     )
     * ) 
     */
    public function changePhoto(Request $request){
        $json = $request->getContent();
        $data = json_decode($json);

        $validator = Validator::make(json_decode($json, true),[
            'profile_pic' => 'required',
        ]);
        if ($validator ->fails())
        {
            return response()->json(['Errors' => $validator ->errors()],400);
        }
          
        try
        {
            $user = $request->user();
            $image_data = $data->profile_pic;

            // Crea un archivo temporal
            $temp_file = tempnam(sys_get_temp_dir(), 'img');
            file_put_contents($temp_file, base64_decode($image_data));
            // Crear un objeto UploadedFile a partir del archivo temporal
            $file = new UploadedFile($temp_file, $user->name.'.png', null, null, true);

            
            // Guardar los datos del archivo cargado en la base de datos y convertir la imagen a url
            $file->storeAs('public', $user->name.'.png');
            $url = Storage::url($user->name.'.png');
            $finalUrl = 'http://127.0.0.1:8000'.$url;
            $user->profile_pic = $finalUrl;
            $user->save();  
            
        }
        catch(\Exception $e)
        {
            return response([
                "message" => "An error has occurred"
            ]);
        }

        return response()->json(['message' => 'Photo changed correctly'], 200);
    }

//  BORRAR CUENTA
    /**
     * Deletes the specified resource.
     * Deletes the user associated with the profile.
     * @param string $password
     * @return \Illuminate\Http\Response
     * 
     * @OA\Delete(
     *     path="/api/user/delete-user",
     *     tags={"user"},
     *     summary="Deletes the user.",
     *      @OA\Parameter(
     *         description="Password of the user",
     *         in="path",
     *         name="password",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="string", value="Ud345678", summary="Password of the user")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User was deleted"
     *     ),
     *      @OA\Response(
     *         response=400,
     *         description="Password is not valid"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="An error occurred."
     *     )
     * ) 
     */
    public function deleteUser(Request $request){
        $json = $request->getContent();
        $data = json_decode($json);
        $validator = Validator::make(json_decode($json, true),[
            'password' => 'required',
        ]);

        if ($validator ->fails())
        {
            return response()->json(['Errors' => $validator ->errors()],400);
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
                if ($user->profile_pic) {
                Storage::delete($user->profile_pic);
                }
                DB::table('plays')->where('user_id', $user->id)->delete();
                $user->delete();
            }
        }
        
        catch(\Exception $e)
        {
            return response([
                "message" => "An error has occurred"
            ], 500);
        }

        return response()->json(['message' => 'User Delete'], 200);
    }
}
