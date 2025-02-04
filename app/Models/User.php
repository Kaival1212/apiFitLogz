<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable , HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    function getTodaysFoods(){
        return $this->foods->where('created_at', '>=', Carbon::today())->sortByDesc('created_at');
    }

    function getTodaysCalories(){
        return $this->getTodaysFoods()->sum('calories');
    }

    function getTodaysProtein(){
        return $this->getTodaysFoods()->sum('protein');
    }

    function getTodaysCarbs(){
        return $this->getTodaysFoods()->sum('carbs');
    }

    function getTodaysFat(){
        return $this->getTodaysFoods()->sum('fat');
    }

    function userWeights(){
        return $this->hasMany(UserWeight::class);
    }

    function foods(){
        return $this->hasMany(UserFood::class);
    }
}
