<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    //
    protected $fillable = [
        'item',
        'description',
        'quantity',
        'date_in',
        'purchased_by',
        'last_disbursed',
        'quantity_disbursed',
        'date_disbursed',
        'disbursed_to',
        'purpose',
        'current_quantity',
        'created_by'
    ];
}
