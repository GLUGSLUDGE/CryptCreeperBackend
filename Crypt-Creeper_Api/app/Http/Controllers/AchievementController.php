<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
/**
 * @OA\Info(
 *      version="1.0.0", 
 *      title="Crypt Creeper",
 *      description="API functions for Crypt Creeper.",
 *      @OA\Contact(
 *          email="glugsludge@gmail.com"
 *      ),
 * )
 */
class AchievementController extends Controller
{
    /**
    * Display a listing of the resource.
    * Shows a list of all the Achievements in the database.
    * @return \Illuminate\Http\Response
    *
    * @OA\Get(
    *     path="/api/achievements",
    *     tags={"achievements"},
    *     summary="Shows an index of achievments",
    *     @OA\Response(
    *         response=200,
    *         description="Shows all achievements."
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="An error ocurred."
    *     )
    * ) 
    */
    function list(){
        try {
            $ach = Achievement::all();
            return response()->json([
                'achievements' => $ach
            ], 200); 
        } catch (Exception $e){
            return response()->json([
                'message' => 'An error ocurred connecting to the server.'
            ], 500); 
        }
    }
    /**
     * Display the specified resource.
     * Shows if the achievement has been unlocked by the user.
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * @OA\Get(
     *     path="/api/achievements/check",
     *     tags={"achievements"},
     *     summary="Shows whether the achievement was unlocked or not by the logged user.",
     *     @OA\Parameter(
     *         description="ID of the achievement.",
     *         in="path",
     *         name="achievement_id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="1", summary="ID of the achievement to check")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Shows if the achievement was unlocked."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Achievement entered was invalid."
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="An error occurred."
     *     )
     * ) 
     */
    function check(Request $request){
        $json = $request->getContent();
        $validator = Validator::make(json_decode($json, true),[
            'achievement_id'=>'required|exists:achievements,id',
        ]);
        if ($validator->fails()){
            return response()->json([
                'message' => ['Error validating the inputs.'],
                'errors' => $validator->errors()
            ], 422);
        }
        try{
            $user = $request->user();
            $id = $user->id;
            $achs = DB::table('achievement_user')
            ->where('user_id','=',$id)
            ->where('achievement_id','=',$request->achievement_id)
            ->get();
            if (!$achs->first()) {
                return response()->json([
                    'status' => false,
                    'message' => 'You do not have this achievement'
                ], 200); 
            } else {
                return response()->json([
                    'status' => true,
                    'message' => 'You have this achievement'
                ], 200); 
            }
        }catch (Exception $e){
            return response()->json([
                'message' => 'An error ocurred connecting to the server.'
            ], 500); 
        }
        
    }
     /**
     * Changes the specified resource.
     * Unlocks the achievement for the player that is logged in.
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * @OA\Post(
     *     path="/api/achievements/add",
     *     tags={"achievements"},
     *     summary="Unlocks the achievement for the logged user.",
     *     @OA\Parameter(
     *         description="ID of the achievement.",
     *         in="path",
     *         name="achievement_id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="1", summary="ID of the achievement to unlock")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unlocks the achievment."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Achievement entered was invalid."
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="An error occurred."
     *     )
     * ) 
     */
    function add(Request $request){
        $json = $request->getContent();
        $validator = Validator::make(json_decode($json, true),[
            'achievement_id'=>'required|exists:achievements,id',
        ]);
        if ($validator->fails()){
            return response()->json([
                'message' => ['Error validating the inputs.'],
                'errors' => $validator->errors()
            ], 422);
        }
        $user = $request->user();
        try{
            $ach = Achievement::find($request->achievement_id);
            $ach->users()->attach($user);
        }catch(Exception $e){
            return response()->json([
                'message' => ['There was an error with the server.']
            ], 500);
        }
        return response()->json([
            'message' => 'Achievement added to the user successfuly.'
        ], 200); 
    }
     /**
     * Display the specified resource.
     * Shows all the achievements unlocked by the player
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * @OA\Get(
     *     path="/api/achievements/player",
     *     tags={"achievements"},
     *     summary="Shows all achievements unlocked by a player.",
     *     @OA\Parameter(
     *         description="ID of the player.",
     *         in="path",
     *         name="user_id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="1", summary="ID of the player")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Shows a list of unlocked achivements."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Player entered was invalid."
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="An error occurred."
     *     )
     * ) 
     */
    function player(Request $request){
        $json = $request->getContent();
        $validator = Validator::make(json_decode($json, true),[
            'user_id'=>'required|exists:users,id',
        ]);
        if ($validator->fails()){
            return response()->json([
                'message' => ['Error validating the inputs.'],
                'errors' => $validator->errors()
            ], 422);
        }
        try{
            $user_id = $request->user_id;
            $achs = DB::table('achievement_user')
            ->join('achievements', 'achievement_user.achievement_id', '=', 'achievements.id')
            ->select('achievements.name','achievements.description','achievements.icon_name')
            ->where('user_id', $user_id)
            ->get();
            return response()->json([
                'achivements' => $achs
            ], 200);
        }catch(Exception $e){
            return response()->json([
                'message' => ['There was an error with the server.']
            ], 500);
        }
        
    }
}
