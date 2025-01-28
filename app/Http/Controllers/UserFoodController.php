<?php

namespace App\Http\Controllers;

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
            'barcode' => 'required',
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

        $foofd = $user->foods()->create([
            'name' => $request->name,
            'barcode' => $request->barcode,
            'calories' => $request->calories,
            'protein' => $request->protein,
            'carbs' => $request->carbs,
            'fat' => $request->fat
        ]);

        info($foofd);

        return response()->json(['message' => 'Food added successfully']);

    }

}
