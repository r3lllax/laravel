<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftWorker extends Model
{
    use HasFactory;

    protected $table = 'shift_workers';
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
