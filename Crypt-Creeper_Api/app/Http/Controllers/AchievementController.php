<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AchievementController extends Controller
{
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
    function player(Request $request){
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
