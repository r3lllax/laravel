<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function status()
    {
        return $this->belongsTo(StatusOrder::class,'status_order_id');

    }

    public function user()
    {
        return $this->hasOneThrough(User::class,ShiftWorker::class,'id','id','shift_worker_id','user_id');
    }

    public function positions()
    {
        return $this->hasMany(OrderMenu::class);
    }

    public function workShift()
    {
        return $this->hasOneThrough(WorkShift::class,ShiftWorker::class,'id','id','shift_worker_id','work_shift_id');
    }
}

