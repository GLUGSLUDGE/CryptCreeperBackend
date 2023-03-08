<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Play;

class PlayController extends Controller
{
//  SAVE THE POINTS
    /**
     * Saves the specified resource.
     * Saves the points to the database.
     * @param  int  $points
     * @return \Illuminate\Http\Response
     * @OA\Post(
     *     path="/api/plays/save_points",
     *     tags={"plays"},
     *     summary="Saves the points to the database associated with the logged in user.",
     *     @OA\Parameter(
     *         description="Points to save.",
     *         in="path",
     *         name="points",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         @OA\Examples(example="int", value="900", summary="Points to save")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Points have been saved."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="No points value was introduced."
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="An error occurred."
     *     )
     * ) 
     */
    public function save_points(Request $request) {
        $json = $request->getContent();
        $data = json_decode($json);

        $validator = Validator::make(json_decode($json, true),[
            'points' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'Errors' => $validator->errors(),
            ], 422);
        } else {
            try {
                $user = $request->user();
                $user_id = $user->id;
                $play = new Play();
                $play->user_id = $user_id;
                $play->points = $data->points;
                $play->save();
            } catch(Exception $e) {
                return response([
                    'message' => 'There was an error saving the points'
                ]);
            }
        }
        return response([
            'message' => 'Points saved correctly',
            'Points' => $play
        ], 200);
    }
//  SHOW THE HIGHER SCORE OF THE PLAYER
    /**
     * Display the specified resource.
     * Shows the higher score of the player
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * @OA\Get(
     *     path="/api/plays/get_higher_points",
     *     tags={"plays"},
     *     summary="Shows highest score by the player.",
     *     @OA\Response(
     *         response=200,
     *         description="Shows the highest score from the logged in player."
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="An error occurred."
     *     )
     * ) 
     */
    public function get_higher_points(Request $request) {
        try{
            $user = $request->user();
            $maxPoints = Play::select(DB::raw('MAX(points) as SCORE'))
                ->where('user_id', $user->id)
                ->get();
    
            return response([
                'MAXSCORE' => $maxPoints
            ], 200);
        } catch(Exception $e){
            return response([
                'message' => "an error has occurred"
            ], 500);
        }
        
        
    }
    //abby - Get top 10 players
    /**
     * Display the specified resource.
     * Shows the top 10 players
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * @OA\Get(
     *     path="/api/plays/leaderboards",
     *     tags={"plays"},
     *     summary="Shows the 10 highest ranked players",
     *     @OA\Response(
     *         response=200,
     *         description="List of players"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="An error occurred."
     *     )
     * ) 
     */
    public function leaderboard(){
        try{
            $plays = DB::table('plays')
            ->join('users', 'plays.user_id', '=', 'users.id')
            ->join('factions', 'users.faction_id', '=', "factions.id")
            ->select(DB::raw('user_id,MAX(points) as points'), 'users.name as username', 'factions.name as faction')
            ->groupBy('user_id')
            ->orderBy('points', 'desc')
            ->limit(10)
            ->get();
            return response([
                'LEADERBOARD' => $plays
            ]);
        }catch(Exception $e){
            return response([
                'message' => "There was an error retrieving the leaderboard."
            ], 500); 
        }
    }
    //abby - Faction leaderboards
    /**
     * Display the specified resource.
     * Shows the Faction rankings.
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * @OA\Get(
     *     path="/api/factions/leaderboard",
     *     tags={"plays"},
     *     summary="Ranks the factions",
     *     @OA\Response(
     *         response=200,
     *         description="List of factions"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="An error occurred."
     *     )
     * ) 
     */
    public function factionleaderboard(){
        try {
            $plays = DB::table('plays')
            ->join('users', 'plays.user_id', '=', 'users.id')
            ->join('factions', 'users.faction_id', '=', "factions.id")
            ->select(DB::raw('faction_id,SUM(points) as points'), 'factions.name')
            ->groupBy('factions.id')
            ->get();
        } catch(Exception $e){
            return response([
                'message' => "There was an error retrieving the leaderboard."
            ], 500);
        }
        return response([
            'LEADERBOARD' => $plays
        ], 200);
    }
}
