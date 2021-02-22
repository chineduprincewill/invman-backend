<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Created extends Model
{
    //
    protected $fillable = [
        'url',
        'username',
        'password'
    ];
}
