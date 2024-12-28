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
            'weight' => 'required|numeric',
            'unit' => 'required|in:kg,lb',
        ]);

        $user = auth()->user();
        $user->userWeights()->create([
            'weight' => $request->weight,
            'unit' => $request->unit,
        ]);

        return response()->json([
            'message' => 'Weight added successfully'
        ]);
    }
}
