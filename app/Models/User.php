<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'api_token',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'role',
        'role_id',
        'password',
        'patronymic',
        'surname',
        'photo_file',
        'api_token',
        'created_at',
        'updated_at',
    ];
    protected $guarded =[];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole($roles)
    {
     return in_array($this->role->code,$roles);
    }

    public function shiftWorkers()
    {
        return $this->hasMany(ShiftWorker::class);
    }

    public function GenerateToken()
    {
        $this->update([
           'api_token'=>Str::random(25),
        ]);
        return$this->api_token;
    }

    public function logout()
    {
        $this->update([
            'api_token' => null
        ]);
    }
}
