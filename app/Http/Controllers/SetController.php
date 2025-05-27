<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
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

    // Get more comprehensive set history for better analysis
    $recentSets = Set::where('user_id', $userId)
                     ->where('exercise_id', $exerciseId)
                     ->latest()
                     ->take(5)
                     ->get();

    $allSets = Set::where('user_id', $userId)
                  ->where('exercise_id', $exerciseId)
                  ->latest()
                  ->take(15)
                  ->get();

    if ($recentSets->isEmpty()) {
        return response()->json([
            'recommendation' => 'Starting fresh? Begin with a weight that allows 8-12 clean reps with 2-3 reps in reserve. Focus on perfecting your form — strength will follow naturally.',
        ]);
    }

    $exercise = Exercise::find($exerciseId);
    $exerciseName = $exercise->name;

    // Determine exercise type for tailored recommendations
    $isCompound = $this->isCompoundExercise($exerciseName);
    $isCardio = $this->isCardioExercise($exerciseName);

    if ($isCardio) {
        return $this->getCardioRecommendation($recentSets, $exerciseName);
    }

    $lastSet = $recentSets->first();
    $lastWeight = $lastSet->weight;
    $lastReps = $lastSet->reps;
    $lastIntensity = $lastSet->intensity;

    // Calculate trending metrics
    $avgReps = $recentSets->avg('reps');
    $intensityScore = $recentSets->map(fn($s) => $this->getIntensityValue($s->intensity))->avg();
    $consistencyDays = $this->getConsistencyDays($allSets);

    // Check for strength trends
    $strengthTrend = $this->analyzeStrengthTrend($recentSets);
    $volumeTrend = $this->analyzeVolumeTrend($recentSets);

    // Progressive overload decision matrix
    $recommendation = $this->generateProgressiveRecommendation(
        $lastWeight,
        $avgReps,
        $intensityScore,
        $strengthTrend,
        $volumeTrend,
        $isCompound,
        $consistencyDays,
        $exerciseName
    );

    return response()->json(['recommendation' => $recommendation]);
}

private function isCompoundExercise($exerciseName)
{
    $compoundKeywords = ['squat', 'deadlift', 'bench', 'press', 'row', 'pull'];
    $name = strtolower($exerciseName);

    return collect($compoundKeywords)->some(fn($keyword) => str_contains($name, $keyword));
}

private function isCardioExercise($exerciseName)
{
    $cardioKeywords = ['treadmill', 'cycling', 'jump rope', 'rowing', 'hiit', 'run'];
    $name = strtolower($exerciseName);

    return collect($cardioKeywords)->some(fn($keyword) => str_contains($name, $keyword));
}

private function getCardioRecommendation($recentSets, $exerciseName)
{
    $avgReps = $recentSets->avg('reps'); // Using reps as time/duration
    $lastIntensity = $recentSets->first()->intensity;

    $recommendations = [
        'Easy' => "Great active recovery session! Consider increasing duration by 2-3 minutes or adding light intervals.",
        'Moderate' => "Solid cardio work. Try increasing intensity with intervals or extend by 5 minutes.",
        'Hard' => "Excellent intensity! Maintain this effort or add complexity with varied intervals.",
        'Failure' => "Maximum effort achieved! Next session, focus on maintaining high intensity for longer duration."
    ];

    $baseRec = $recommendations[$lastIntensity] ?? "Keep up the cardio work!";

    return response()->json([
        'recommendation' => "{$baseRec} Consistency in cardio builds endurance — every session counts toward your goals."
    ]);
}

private function getIntensityValue($intensity)
{
    return match ($intensity) {
        'Easy' => 1,
        'Moderate' => 2,
        'Hard' => 3,
        'Failure' => 4,
        default => 2
    };
}

private function getConsistencyDays($allSets)
{
    if ($allSets->count() < 2) return 1;

    $dates = $allSets->pluck('created_at')->map(fn($date) => $date->toDateString())->unique();
    return $dates->count();
}

private function analyzeStrengthTrend($sets)
{
    if ($sets->count() < 3) return 'stable';

    $weights = $sets->pluck('weight')->reverse()->values();
    $increases = 0;
    $decreases = 0;

    for ($i = 1; $i < $weights->count(); $i++) {
        if ($weights[$i] > $weights[$i-1]) $increases++;
        if ($weights[$i] < $weights[$i-1]) $decreases++;
    }

    if ($increases > $decreases) return 'increasing';
    if ($decreases > $increases) return 'decreasing';
    return 'stable';
}

private function analyzeVolumeTrend($sets)
{
    if ($sets->count() < 3) return 'stable';

    $volumes = $sets->map(fn($s) => $s->weight * $s->reps)->reverse()->values();
    $recentAvg = $volumes->take(2)->avg();
    $olderAvg = $volumes->skip(2)->avg();

    $percentChange = $olderAvg > 0 ? (($recentAvg - $olderAvg) / $olderAvg) * 100 : 0;

    if ($percentChange > 5) return 'increasing';
    if ($percentChange < -5) return 'decreasing';
    return 'stable';
}

private function generateProgressiveRecommendation($lastWeight, $avgReps, $intensityScore, $strengthTrend, $volumeTrend, $isCompound, $consistencyDays, $exerciseName)
{
    $weightIncrement = $isCompound ? 2.5 : 1.25;
    $weightChange = 0;
    $repRange = '';
    $motivation = '';

    // Advanced progression logic
    if ($avgReps >= 12 && $intensityScore <= 2.5) {
        // Easy progression - ready for weight increase
        $weightChange = $weightIncrement * 2;
        $repRange = '8-10';
        $motivation = 'Excellent progress! Time to challenge yourself with heavier weight.';

    } elseif ($avgReps >= 10 && $intensityScore <= 2.5 && $strengthTrend === 'stable') {
        // Moderate progression
        $weightChange = $weightIncrement;
        $repRange = '8-12';
        $motivation = 'Solid consistency. Let\'s keep building strength progressively.';

    } elseif ($avgReps >= 8 && $intensityScore <= 3 && $volumeTrend === 'increasing') {
        // Conservative progression
        $weightChange = $weightIncrement * 0.5;
        $repRange = '6-10';
        $motivation = 'Great volume increase! Small weight bump to maintain momentum.';

    } elseif ($avgReps < 6 || $intensityScore >= 3.5) {
        // Deload recommendation
        $weightChange = -$weightIncrement;
        $repRange = '10-12';
        $motivation = 'Strategic deload for better form and recovery. Progress isn\'t always linear.';

    } elseif ($strengthTrend === 'decreasing' && $consistencyDays < 3) {
        // Focus on consistency
        $weightChange = 0;
        $repRange = '8-12';
        $motivation = 'Focus on consistent training. Strength returns with regular practice.';

    } else {
        // Maintain current load
        $weightChange = 0;
        $repRange = '8-12';
        $motivation = 'Perfect your technique at this weight. Mastery leads to strength.';
    }

    // Calculate and round recommended weight
    $newWeight = max($lastWeight + $weightChange, 2.5);
    $recommendedWeight = round($newWeight / 1.25) * 1.25;

    // Add specific exercise insights
    $exerciseInsight = $this->getExerciseSpecificInsight($exerciseName, $recommendedWeight, $lastWeight);

    return "Next session: {$recommendedWeight} kg for {$repRange} reps. {$motivation} {$exerciseInsight}";
}

private function getExerciseSpecificInsight($exerciseName, $recommendedWeight, $lastWeight)
{
    $name = strtolower($exerciseName);

    if (str_contains($name, 'squat')) {
        return $recommendedWeight > $lastWeight ?
            'Focus on depth and knee tracking.' :
            'Perfect your squat depth and core engagement.';
    }

    if (str_contains($name, 'deadlift')) {
        return $recommendedWeight > $lastWeight ?
            'Maintain neutral spine throughout the lift.' :
            'Focus on hip hinge pattern and bar path.';
    }

    if (str_contains($name, 'bench')) {
        return $recommendedWeight > $lastWeight ?
            'Control the descent and drive through your feet.' :
            'Perfect your arch and shoulder blade retraction.';
    }

    if (str_contains($name, 'curl')) {
        return 'Control the eccentric and avoid momentum.';
    }

    if (str_contains($name, 'press')) {
        return 'Maintain core stability throughout the movement.';
    }

    return 'Focus on controlled movement and full range of motion.';
}
}
