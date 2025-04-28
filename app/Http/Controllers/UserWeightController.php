<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserWeightController extends Controller
{
    public function index(User $user)
    {
        $user = auth()->user();
        $weights = $user->userWeights()->latest()->get();

        return response()->json([
           "weights" => $weights
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'weight' => 'required|numeric|min:1',
            'unit'   => 'sometimes|in:kg,lb',
        ]);

        // user() comes from Sanctum / auth:sanctum middleware
        $weight = $request->user()->userWeights()->create([
            'weight' => $request->weight,
            'unit'   => $request->unit ?? 'kg',
        ]);

        return response()->json($weight, 201);
    }
}
