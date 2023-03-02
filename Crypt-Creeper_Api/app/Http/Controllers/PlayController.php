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
        $maxPoints = Play::select(DB::raw('MAX(points) as High_Score'))
            ->where('user_id', $user->id)
            ->get();

        return response([
            'Puntuación máxima' => $maxPoints
        ]);
        
    }
}
