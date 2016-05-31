<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fcolor extends Model
{
    //protected $table = 'fcolors';
    protected $fillable = array('restaurant', 'color');
}
