<?php

/**
 * Created by PhpStorm.
 * User: devert
 * Date: 8/31/19
 * Time: 12:06 AM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'destination',
        'service_code',
        'callback_url',
        'amount',
        'internal_id',
        'external_id',
        'status',
        'customer_id',
        'items'
    ];

    protected $casts = [
        'amount' => 'double',
    ];
}