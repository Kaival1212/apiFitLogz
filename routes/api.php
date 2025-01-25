<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use GuzzleHttp\Client;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/weights', [\App\Http\Controllers\UserWeightController::class, 'index'])->middleware('auth:sanctum');
Route::post('/weights', [\App\Http\Controllers\UserWeightController::class, 'store'])->middleware('auth:sanctum');

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
        'daily_calories_limit' => 'required',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'height' => $request->height,
        'weight' => $request->weight,
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

Route::get('/openfoodfacts/product/{id}', function (String $id) {

    info("Fetching product with id: $id");

    $client = new Client();
    $url = "https://api.openfoodfacts.org/api/v3/product/$id.json";

    $response = $client->get($url);
    $jsondata = $response->getBody()->getContents();
    $data = json_decode($jsondata,true);
    $product = $data["product"];
    $response_product = [
        'id' => $product["_id"],
        'name' => $product["product_name"] ?? null,
        'name_en' => $product["product_name_en"] ?? null,
        'quantity' => $product["product_quantity"],
        'quantity_unit' => $product["product_quantity_unit"],
        'image_front_small_url' => $product["image_front_small_url"] ?? null,

        'carbohydrates' => $product["nutriments"]["carbohydrates"] ?? null,
        'carbohydrates_100g' => $product["nutriments"]["carbohydrates_100g"] ?? null,
        'carbohydrates_serving' => $product["nutriments"]["carbohydrates_serving"] ?? null,
        'carbohydrates_unit' => $product["nutriments"]["carbohydrates_unit"] ?? null,

        'energy' => $product["nutriments"]["energy"] ?? null,
        'energy_kcal' => $product["nutriments"]["energy-kcal"] ?? null,
        'energy_kcal_100g' => $product["nutriments"]["energy-kcal_100g"] ?? null,
        'energy_kcal_serving' => $product["nutriments"]["energy-kcal_serving"] ?? null,
        'energy_kcal_unit' => $product["nutriments"]["energy-kcal_unit"] ?? null,

        'fat' => $product["nutriments"]["fat"] ?? null,
        'fat_100g' => $product["nutriments"]["fat_100g"] ?? null,
        'fat_serving' => $product["nutriments"]["fat_serving"] ?? null,
        'fat_unit' => $product["nutriments"]["fat_unit"] ?? null,

        'proteins' => $product["nutriments"]["proteins"] ?? null,
        'proteins_100g' => $product["nutriments"]["proteins_100g"] ?? null,
        'proteins_serving' => $product["nutriments"]["proteins_serving"] ?? null,
        'proteins_unit' => $product["nutriments"]["proteins_unit"] ?? null,

        'sugar' => $product["nutriments"]["sugars"] ?? null,
        'sugar_100g' => $product["nutriments"]["sugars_100g"] ?? null,
        'sugar_serving' => $product["nutriments"]["sugars_serving"] ?? null,
        'sugar_unit' => $product["nutriments"]["sugars_unit"] ?? null,
    ];

    return response()->json($response_product);

});
