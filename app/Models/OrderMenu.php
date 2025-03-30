<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderMenu extends Model
{
    protected $table = 'order_menus';
    use HasFactory;

    public function product()
    {
        return $this->belongsTo(Menu::class,'menu_id');
    }
}
