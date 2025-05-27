<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Exercise;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create test user
        $user = User::factory()->create([
            'name' => 'Kaival Patel',
            'email' => 'kaival225@gmail.com',
            'password' => Hash::make('password'),
            'height' => 167,
            'weight' => 86,
            'age' => 20,
            'goal' => 'Recomposition',
            'goal_weight' => 88,
            'activity_level' => 'High',
            'daily_calories_goal' => 2800,
            'daily_calories_limit' => 3000,
            'daily_protein_limit' => 180,
            'daily_carbs_limit' => 300,
            'daily_fat_limit' => 90,
            'daily_sugar_limit' => 50,
            'daily_steps_goal' => 8000,
        ]);

        // Exercises using 'muscle_group' field to store the workout day
$exercises = [
    // Monday – Chest + Shoulders
    ['Barbell Bench Press 3 sets of 10 reps', 'Monday'],
    ['Incline Dumbbell Press 3 sets of 10 reps', 'Monday'],
    ['Shoulder Press 3 sets of 12 reps', 'Monday'],
    ['Lateral Raise 3 sets of 15 reps', 'Monday'],

    // Tuesday – Quads + Legs
    ['Back Squat 4 sets of 8 reps', 'Tuesday'],
    ['Leg Press 4 sets of 10 reps', 'Tuesday'],
    ['Leg Curl 3 sets of 12 reps', 'Tuesday'],
    ['Calf Raise 3 sets of 20 reps', 'Tuesday'],

    // Wednesday – Back + Biceps
    ['Deadlift 3 sets of 6 reps', 'Wednesday'],
    ['Barbell Row 3 sets of 10 reps', 'Wednesday'],
    ['Lat Pulldown 3 sets of 12 reps', 'Wednesday'],
    ['Face Pull 3 sets of 15 reps', 'Wednesday'],
    ['Barbell Curl 3 sets of 10 reps', 'Wednesday'],

    // Thursday – Shoulders + Arms
    ['Overhead Press 3 sets of 10 reps', 'Thursday'],
    ['Rear Delt Fly 3 sets of 15 reps', 'Thursday'],
    ['Hammer Curl 3 sets of 10 reps', 'Thursday'],
    ['Triceps Pushdown 3 sets of 12 reps', 'Thursday'],
    ['Overhead Triceps Extension 3 sets of 10 reps', 'Thursday'],

    // Friday – Glutes + Hamstrings
    ['Romanian Deadlift 5 sets of 10 reps', 'Friday'],
    ['Bulgarian Split Squat 4 sets of 8 reps', 'Friday'],
    ['Glute Bridge 3 sets of 12 reps', 'Friday'],
    ['Leg Curl (Machine) 3 sets of 12 reps', 'Friday'],
    ['Standing Calf Raise 3 sets of 20 reps', 'Friday'],

    // Saturday – Cardio / Active Recovery
    ['Treadmill Run 20 mins', 'Saturday'],
    ['Cycling 30 mins', 'Saturday'],
    ['Jump Rope 3 rounds of 1 min', 'Saturday'],
    ['Rowing Machine 15 mins', 'Saturday'],
    ['HIIT Circuit 4 rounds', 'Saturday'],
];


        foreach ($exercises as [$name, $day]) {
            Exercise::create([
                'user_id' => $user->id,
                'name' => $name,
                'muscle_group' => $day, // Used as a UI sorting filter for workout days
            ]);
        }
    }
}
