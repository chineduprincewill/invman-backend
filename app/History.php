<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    //
    protected $fillable = [
        'item_id',
        'quantity_disbursed',
        'disbursed_to',
        'purpose',
        'disbursed_by'
    ];
}
