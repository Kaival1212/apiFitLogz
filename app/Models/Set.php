<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Set extends Model
{
    /** @use HasFactory<\Database\Factories\SetFactory> */
    use HasFactory;

    protected $fillable = [
        'exercise_id',
        'reps',
        'weight',
        'intensity',
        'user_id',
    ];

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
    public function user()     { return $this->belongsTo(User::class); }

}
