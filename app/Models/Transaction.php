<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['seller_id', 'buyer_id', 'currency_from', 'currency_to', 'amount_from', 'amount_to', 'system_fee'];
}
