<?php

namespace App\Http\Controllers;

use App\Models\Set;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetController extends Controller
{
    public function index(Request $request)
    {
        $query = Set::where('user_id', $request->user()->id);

        if ($request->filled('exercise_id')) {
            $query->where('exercise_id', $request->exercise_id);
        }

        return $query->latest()->get();
    }


    public function store(Request $request)
    {
        $request->validate([
            'exercise_id' => 'required|exists:exercises,id',
            'weight'      => 'required|numeric|min:0',
            'reps'        => 'required|integer|min:1',
            'intensity'   => 'required|in:Easy,Moderate,Hard,Failure',
        ]);

        $set = Set::create([
            'user_id'     => Auth::id(),
            'exercise_id' => $request->exercise_id,
            'weight'      => $request->weight,
            'reps'        => $request->reps,
            'intensity'   => $request->intensity,
        ]);

        return response()->json($set->load('exercise'), 201);
    }

    public function show(Set $set)
    {
        return $set;
    }

    public function update(Request $request, Set $set)
    {

        $request->validate([
            'weight'    => 'sometimes|required|numeric|min:0',
            'reps'      => 'sometimes|required|integer|min:1',
            'intensity' => 'sometimes|required|in:Easy,Moderate,Hard,Failure',
        ]);

        $set->update($request->only(['weight', 'reps', 'intensity']));

        return $set->fresh()->load('exercise');
    }

    public function destroy(Set $set)
    {
        $set->delete();

        return response()->noContent();
    }

public function recommendation(Request $request)
{
    $request->validate([
        'exercise_id' => 'required|exists:exercises,id',
    ]);

    $userId = Auth::id();
    $exerciseId = $request->exercise_id;

    $sets = Set::where('user_id', $userId)
               ->where('exercise_id', $exerciseId)
               ->latest()
               ->take(3)
               ->get();

    if ($sets->isEmpty()) {
        return response()->json([
            'recommendation' => 'No recent sets found. Start with a moderate weight and focus on good form. Stay consistent and results will follow.',
        ]);
    }

    $lastSet = $sets->first();
    $lastWeight = $lastSet->weight;

    $avgReps = $sets->avg('reps');
    $intensityScore = $sets->map(fn($s) => match ($s->intensity) {
        'Easy' => 1, 'Moderate' => 2, 'Hard' => 3, 'Failure' => 4,
    })->avg();

    $weightChange = 0;
    $motivation = '';

    if ($avgReps >= 10 && $intensityScore <= 2) {
        $weightChange = 2.5;
        $motivation = 'You’re progressing well — time to challenge yourself.';
    } elseif ($avgReps >= 8 && $intensityScore <= 3) {
        $weightChange = 1.25;
        $motivation = 'Great consistency. Let’s keep moving forward.';
    } elseif ($avgReps < 6 || $intensityScore >= 3.5) {
        $weightChange = -2.5;
        $motivation = 'Take a step back to focus on form — progress isn’t always linear.';
    } else {
        $motivation = 'Keep showing up — strength is built over time.';
    }

    $rawWeight = $lastWeight + $weightChange;
    $roundedWeight = round($rawWeight / 2.5) * 2.5;
    $roundedWeight = max($roundedWeight, 5);

    return response()->json([
        'recommendation' => "Based on your recent performance, try lifting {$roundedWeight} kg for 8–10 reps in your next session. {$motivation}"
    ]);
}


}
