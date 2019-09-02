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
 * @property string internal_id
 * @property string external_id
 * @property string meter_code
 * @property double amount
 * @property string callback_url
 * @property string status
 * @property string meter_id
 */
class Transaction extends Model
{
    protected $fillable = [
        'meter_code',
        'meter_id',
        'internal_id',
        'external_id',
        'energy',
        'amount',
        'token',
        'callback_url',
        'status',
    ];

    protected $casts = [
        'amount' => 'double',
    ];
}