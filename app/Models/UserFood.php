<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFood extends Model
{
    /** @use HasFactory<\Database\Factories\UserFoodFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'calories',
        'protein',
        'carbs',
        'fat',
    ];

    function user(){
        return $this->belongsTo(User::class);
    }
}
