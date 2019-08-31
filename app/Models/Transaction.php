<?php

/**
 * Created by PhpStorm.
 * User: devert
 * Date: 8/31/19
 * Time: 12:06 AM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed internal_id
 * @property mixed external_id
 * @property mixed meter_code
 * @property double amount
 * @property string callback_url
 * @property string status
 */
class Transaction extends Model
{
    protected $fillable = [
        'meter_code',
        'internal_id',
        'external_id',
        'energy',
        'amount',
        'token',
        'callback_url',
    ];

    protected $casts = [
        'amount' => 'double',
    ];
}