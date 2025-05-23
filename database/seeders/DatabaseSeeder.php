<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserWeight;
use App\Models\Exercise;
use App\Models\Set;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Kaival Patel',
            'email' => 'kaival225@gmail.com',
            'password' => Hash::make('password'),
            'height' => '167',
            'weight' => '86',
            'age' => '21',
            'goal' => 'Gain Muscle',
            'goal_weight' => '88',
            'activity_level' => 'High',
            'daily_calories_goal' => '2800',
            'daily_calories_limit' => '3000',
            'daily_protein_limit' => '180',
            'daily_carbs_limit' => '300',
            'daily_fat_limit' => '90',
            'daily_sugar_limit' => '50',
            'daily_steps_goal' => '8000',
        ]);

        // Weight progression over 90 days
        for ($i = 90; $i >= 0; $i--) {
            $progressWeight = 72 + ((90 - $i) * (14 / 90));
            UserWeight::create([
                'user_id' => $user->id,
                'weight' => round($progressWeight + rand(-10, 10) * 0.01, 2),
                'unit' => 'kg',
                'created_at' => Carbon::now()->subDays($i),
                'updated_at' => Carbon::now()->subDays($i),
            ]);
        }

        $exerciseList = collect([
            ['Bench Press', 'Chest', 40, 2.5],
            ['Incline Dumbbell Press', 'Chest', 20, 1.5],
            ['Deadlift', 'Back', 70, 4],
            ['Barbell Row', 'Back', 35, 2],
            ['Lat Pulldown', 'Back', 40, 1.8],
            ['Shoulder Press', 'Shoulders', 20, 1.5],
            ['Lateral Raise', 'Shoulders', 6, 0.5],
            ['Squat', 'Legs', 60, 3.5],
            ['Leg Press', 'Legs', 100, 5],
            ['Calf Raises', 'Legs', 40, 2],
            ['Barbell Curl', 'Arms', 15, 1.2],
            ['Triceps Pushdown', 'Arms', 20, 1.5],
        ]);

        $startDate = Carbon::now()->subDays(90);

        $exerciseList->each(function ($item) use ($user, $startDate) {
            [$name, $group, $startWeight, $increment] = $item;

            $exercise = Exercise::create([
                'user_id' => $user->id,
                'name' => $name,
                'muscle_group' => $group,
            ]);

for ($i = 0; $i < 9; $i++) {
    $rawWeight = $startWeight + ($i * $increment);

    $weight = round($rawWeight / 2.5) * 2.5;

    Set::create([
        'user_id' => $user->id,
        'exercise_id' => $exercise->id,
        'weight' => $weight,
        'reps' => rand(8, 12),
        'intensity' => $i >= 7 ? 'Hard' : ($i >= 4 ? 'Moderate' : 'Easy'),
        'created_at' => $startDate->copy()->addDays($i * 10),
        'updated_at' => $startDate->copy()->addDays($i * 10),
    ]);
}

        });
    }
}
