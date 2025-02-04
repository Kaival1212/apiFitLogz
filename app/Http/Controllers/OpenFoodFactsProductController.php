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

        try {
            $response = $client->get($url);
            $jsondata = $response->getBody()->getContents();
            $data = json_decode($jsondata, true);
            $product = $data["product"];
        }

        catch (Exception $e) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        try{
            $response_product = $this->formatProductResponse($product);
            return response()->json($response_product);
        } catch (Exception $e) {
            return response()->json(['error' => 'API Error']);
        }
    }

        private function formatProductResponse(array $product): array
        {
            $nutriments = $product['nutriments'] ?? [];
            $quantity = $product['product_quantity'] ?? 1;
            $quantityUnit = $product['product_quantity_unit'] ?? 'g';

            // Helper function to calculate per serving value if not provided
            $calculatePerServing = function ($per100g, $quantity) {
                return $per100g !== null ? ($per100g * $quantity) / 100 : null;
            };

            return [
                'id' => $product['_id'],
                'name' => $product['product_name'],
                'name_en' => $product['product_name_en'],
                'quantity' => $quantity,
                'quantity_unit' => $quantityUnit,
                'image_front_small_url' => $product['image_front_small_url'] ?? null,
                'nutrition_data_per' => $product['nutrition_data_per'],

                'carbohydrates_100g' => $nutriments['carbohydrates_100g'] ?? null,
                'carbohydrates_serving' => $nutriments['carbohydrates_serving'] ?? $calculatePerServing($nutriments['carbohydrates_100g'] ?? null, $quantity),
                'carbohydrates_unit' => $nutriments['carbohydrates_unit'] ?? null,

                'energy_kcal_100g' => $nutriments['energy-kcal_100g'] ?? null,
                'energy_kcal_serving' => $nutriments['energy-kcal_serving'] ?? $calculatePerServing($nutriments['energy-kcal_100g'] ?? null, $quantity),
                'energy_kcal_unit' => $nutriments['energy-kcal_unit'] ?? null,

                'fat_100g' => $nutriments['fat_100g'] ?? null,
                'fat_serving' => $nutriments['fat_serving'] ?? $calculatePerServing($nutriments['fat_100g'] ?? null, $quantity),
                'fat_unit' => $nutriments['fat_unit'] ?? null,

                'proteins_100g' => $nutriments['proteins_100g'] ?? null,
                'proteins_serving' => $nutriments['proteins_serving'] ?? $calculatePerServing($nutriments['proteins_100g'] ?? null, $quantity),
                'proteins_unit' => $nutriments['proteins_unit'] ?? null,
            ];
        }
}
