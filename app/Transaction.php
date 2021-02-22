<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    //
    protected $fillable = [
        'account_no',
        'account_name',
        'user_id',
        'phone',
        'transaction_type',
        'amount',
        'balance',
        'teller',
        'paid_with',
        'order_from'
    ];
}
