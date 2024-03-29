<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 3/18/20
 * Time: 8:53 PM
 */

namespace App\Services\Clients\Providers;

use App\Services\Clients\ClientInterface;
use App\Services\Objects\PrepaidMeter;

class TestClient implements ClientInterface
{
    public function search($meterCode): PrepaidMeter
    {
        $meter = new PrepaidMeter();
        $meter->setServiceCode('Test Code')
            ->setMeterCode($meterCode)
            ->setName('Duke')
            ->setAddress('123 Deido Douala');
        return $meter;
    }
    
    public function buy(PrepaidMeter $meter) : string
    {
        return '1254145478745254';
    }
    
    public function status($transaction): string
    {
        return '145845748522155';
    }
    
    /**
     * @return string
     */
    public function getClientName(): string
    {
        return class_basename($this);
    }
}