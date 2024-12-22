<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/weights', [\App\Http\Controllers\UserWeightController::class, 'index'])->middleware('auth:sanctum');

Route::get('/test',
    function (Request $request) {
        return response()->json([
            'message' => 'Hello World!',
        ]);
    }
);

Route::post('/sanctum/token', function (Request $request) {
    try {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'deviceName' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'token' => $user->createToken($request->deviceName)->plainTextToken,
        ]);
    } catch (ValidationException $e) {
        return response()->json(['error' => $e->getMessage()], 422);
    }
});

Route::post("/register", function (Request $request) {
    $request->validate([
        'name' => 'required',
        'email' => 'required|email',
        'password' => 'required',
        'confirmPassword' => 'required|same:password', // 'password_confirmation' => 'required|same:password
        'deviceName' => 'required',
        'weight' => 'required',
        'height' => 'required',
        'age' => 'required|>=18 ',
        'goal' => 'required|in:lose,maintain,gain',
        'goal_weight' => 'required',
        'activity_level' => 'required|in:sedentary,lightly active,moderately active,very active,super active',
        'daily_calories_goal' => 'required',
        'daily_steps_goal' => 'required',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'height' => $request->height,
        'age' => $request->age,
        'goal' => $request->goal,
        'goal_weight' => $request->goal_weight,
        'activity_level' => $request->activity_level,
        'daily_calories_goal' => $request->daily_calories_goal,
        'daily_steps_goal' => $request->daily_steps_goal,
    ]);

    $user->userWeights()->create([
        'weight' => $request->weight,
    ]);

    return $user->createToken($request->deviceName)->plainTextToken;
});

Route::middleware(['auth:sanctum'])->post('/logout', function (Request $request) {
    $request->user()->tokens()->delete();
    return response()->json(['message' => 'Logged out']);
});
