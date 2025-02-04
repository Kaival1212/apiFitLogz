<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class UserFoodController extends Controller
{
    public function store(Request $request){

        $user = $request->user();

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
            return response()->json(['error' => $e], 400);
        }

        $foof = $user->foods()->create([
            'name' => $request->name,
            'calories' => $request->calories,
            'protein' => $request->protein,
            'carbs' => $request->carbs,
            'fat' => $request->fat
        ]);

        return response()->json(['message' => 'Food added successfully']);

    }

    public function index(Request $request)
    {
        $user = $request->user();
        $foods = $user->getTodaysFoods()->values(); // Reset array keys

        if ($foods->isEmpty()) {
            return response()->json(['message' => 'No foods found for today']);
        }

        return response()->json([
            "data" => $foods
        ]);
    }


    public function dailyNutrition(Request $request){

        return response()->json(
            [
                "calories" => $request->user()->getTodaysCalories(),
                "protein" => $request->user()->getTodaysProtein(),
                "carbs" => $request->user()->getTodaysCarbs(),
                "fat" => $request->user()->getTodaysFat(),
            ]
        );

    }
}
