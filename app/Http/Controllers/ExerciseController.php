<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExerciseController extends Controller
{
    /** List all exercises owned by the user */
    public function index()
    {

        return Auth::user()->exercises()->latest()->get();

    }

    /** Create a new exercise */
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'muscle_group' => 'nullable|string|max:255',
        ]);



        $exercise = Auth::user()->exercises()->create($request->only('name', 'muscle_group'));

        return response()->json($exercise, 201);
    }

    /** Show a single exercise (authorised via route-model-binding) */
    public function show(Exercise $exercise)
    {
        // $this->authorize('view', $exercise);   // optional policy
        // load the sets relationship if needed
        $exercise->load('sets');

        return $exercise;
    }

    /** Update an exercise */
    public function update(Request $request, Exercise $exercise)
    {
        // $this->authorize('update', $exercise);

        $request->validate([
            'name'         => 'sometimes|required|string|max:255',
            'muscle_group' => 'sometimes|nullable|string|max:255',
        ]);

        $exercise->update($request->only('name', 'muscle_group'));

        return $exercise->fresh();
    }

    /** Delete an exercise */
    public function destroy(Exercise $exercise)
    {
        // $this->authorize('delete', $exercise);
        $exercise->delete();

        return response()->noContent();
    }
}
