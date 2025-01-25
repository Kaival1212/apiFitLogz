<?php

namespace App\Http\Controllers;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class OpenFoodFactsProductController extends Controller
{
    public function getProduct($id){
        info("Fetching product with id: $id");

        $client = new Client();
        $url = "https://api.openfoodfacts.org/api/v3/product/$id.json";

        try{
            $response = $client->get($url);}
        catch (Exception $e) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        $jsondata = $response->getBody()->getContents();
        $data = json_decode($jsondata,true);
        $product = $data["product"];



        $response_product = [
            'id' => $product["_id"],
            'name' => $product["product_name"],
            'name_en' => $product["product_name_en"],
            'quantity' => $product["product_quantity"] ?? null,
            'quantity_unit' => $product["product_quantity_unit"] ?? null,
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

        try{
            return response()->json($response_product);
        } catch (Exception $e) {
            return response()->json(['error' => 'API Error']);
        }
    }
}
