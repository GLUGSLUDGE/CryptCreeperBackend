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
        ]);
    }
//  GET HIGHER POINTS FROM AN USER
    public function get_higher_points(Request $request) {
        $user = $request->user();
        $maxPoints = Play::select(DB::raw('MAX(points) as SCORE'))
            ->where('user_id', $user->id)
            ->get();

        return response([
            'MAXSCORE' => $maxPoints
        ]);
        
    }
    //abby - Get top 10 players
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
        }catch(Exception $e){
            return response([
                'message' => "There was an error retrieving the leaderboard."
            ], 500); 
        }
        return response([
            'LEADERBOARD' => $plays
        ]);
    }
    //abby - Faction leaderboards
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
