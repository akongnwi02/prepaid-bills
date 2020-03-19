<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 3/14/20
 * Time: 7:27 PM
 */

namespace App\Services\Clients;

use App\Services\Objects\PrepaidMeter;

interface ClientInterface
{
    public function search($meterCode): PrepaidMeter;
    
    public function buy($meterCode, $amount) : string;
}