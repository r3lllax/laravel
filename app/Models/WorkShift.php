<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkShift extends Model
{
    use HasFactory;
    protected $id;
    protected $bearerToken;

    
    public function workers()
    {
        return $this->belongsToMany(User::class,ShiftWorker::class);
    }

    public function orders()
    {
        return $this->hasManyThrough(Order::class,ShiftWorker::class);
    }
}
