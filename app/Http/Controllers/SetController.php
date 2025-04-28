<?php

namespace App\Http\Controllers;

use App\Models\Set;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetController extends Controller
{
    /** List all sets for current user */
    public function index()
    {
        return Set::with('exercise')
                  ->where('user_id', Auth::id())
                  ->latest()
                  ->get();
    }

    /** Store a new set */
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

    /** Show a single set */
    public function show(Set $set)
    {
        return $set->load('exercise');
    }

    /** Update a set */
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

    /** Delete a set */
    public function destroy(Set $set)
    {
        $set->delete();

        return response()->noContent();
    }

    /** GET /sets/recommendation?exercise_id=… */
    public function recommendation(Request $request)
    {
        $request->validate([
            'exercise_id' => 'required|exists:exercises,id',
        ]);

        $lastSet = Set::where('user_id', Auth::id())
                      ->where('exercise_id', $request->exercise_id)
                      ->latest()
                      ->first();

        if (!$lastSet) {
            return response()->json([
                'recommendation' => 'No history yet — start with a moderate weight and good form.'
            ]);
        }

        // --- Simple heuristic ---
        $rec = 'Maintain current weight. Focus on form.';

        if ($lastSet->reps >= 10 && in_array($lastSet->intensity, ['Easy', 'Moderate'])) {
            $rec = 'Nice! Try adding +2.5 kg (or the next plate) next session.';
        } elseif ($lastSet->reps >= 6 && $lastSet->reps <= 8 && $lastSet->intensity === 'Hard') {
            $rec = 'Solid set. Aim for 9-10 reps before adding weight.';
        } elseif ($lastSet->reps < 6 || $lastSet->intensity === 'Failure') {
            $rec = 'Consider dropping the weight a bit to keep good form.';
        }

        return response()->json([
            'last_set'       => $lastSet,
            'recommendation' => $rec,
        ]);
    }
}
