<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 10/19/20
 * Time: 1:34 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Authentication extends Model
{
    protected $fillable = [
        'expires_in',
        'access_token',
        'refresh_token',
        'service_code',
        'type'
    ];
    
    protected $casts = [
        'expires_in' => 'integer',
    ];
}