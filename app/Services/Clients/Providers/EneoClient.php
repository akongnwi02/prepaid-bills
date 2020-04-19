<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 3/18/20
 * Time: 8:27 PM
 */

namespace App\Services\Clients\Providers;


use App\Models\Transaction;
use App\Services\Clients\ClientInterface;
use App\Services\Objects\PrepaidMeter;

class EneoClient implements ClientInterface
{
    /**
     * @param $meterCode
     * @return PrepaidMeter
     */
    public function search($meterCode): PrepaidMeter
    {
        throw new \BadMethodCallException('ENEO service not available');
    }
    
    /**
     * @param PrepaidMeter $meter
     * @return string
     */
    public function buy(PrepaidMeter $meter): string
    {
        throw new \BadMethodCallException('ENEO service not available');
    }
    
    public function status($transaction): string
    {
        throw new \BadMethodCallException('ENEO service not available');
    }
    
    /**
     * @return string
     */
    public function getClientName(): string
    {
        // TODO: Implement getName() method.
    }
}