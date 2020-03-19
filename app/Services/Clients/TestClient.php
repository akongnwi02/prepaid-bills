<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 3/18/20
 * Time: 8:53 PM
 */

namespace App\Services\Clients;


use App\Services\Objects\PrepaidMeter;

class TestClient implements ClientInterface
{
    public function search($meterCode): PrepaidMeter
    {
        // TODO: Implement search() method.
    }
    
    public function buy($meterCode, $amount) : string {
    
    }
}