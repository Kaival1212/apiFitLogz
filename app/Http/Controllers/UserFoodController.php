<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class UserFoodController extends Controller
{
    public function store(Request $request){

        $user = $request->user();

        info($user);
        info($request);
        info("name".$request->barcode);
        try{
        $request->validate([
            'name' => 'required',
            'calories' => 'required',
            'protein' => 'required',
            'carbs' => 'required',
            'fat' => 'required'
        ]);
        }
        catch (\Exception $e){
            info($e);
            return response()->json(['error' => $e], 400);
        }

        $foof = $user->foods()->create([
            'name' => $request->name,
            'calories' => $request->calories,
            'protein' => $request->protein,
            'carbs' => $request->carbs,
            'fat' => $request->fat
        ]);

        info($foof);

        return response()->json(['message' => 'Food added successfully']);

    }

    public function index(Request $request){
        $user = $request->user();
        //dd($user->getTodaysFoods() , $user->getTodaysCalories() , $user->getTodaysProtein() , $user->getTodaysCarbs() , $user->getTodaysFat());
        $foods = $user->getTodaysFoods();

        if ($foods->count() == 0){
            return response()->json(['message' => 'No foods found for today']);
        }
        return response()->json($user->getTodaysFoods());
    }

}
