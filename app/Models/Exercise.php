<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    /** @use HasFactory<\Database\Factories\ExerciseFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'muscle_group',
    ];

    public function sets()
    {
        return $this->hasMany(Set::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
