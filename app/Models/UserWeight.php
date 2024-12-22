<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWeight extends Model
{
    /** @use HasFactory<\Database\Factories\UserWeightFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'weight',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
