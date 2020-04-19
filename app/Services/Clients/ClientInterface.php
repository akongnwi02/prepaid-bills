<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 3/14/20
 * Time: 7:27 PM
 */

namespace App\Services\Clients;

use App\Exceptions\BadRequestException;
use App\Exceptions\GeneralException;
use App\Models\Transaction;
use App\Services\Objects\PrepaidMeter;

interface ClientInterface
{
    /**
     * @param $meterCode
     * @return PrepaidMeter
     * @throws BadRequestException
     * @throws GeneralException
     */
    public function search($meterCode): PrepaidMeter;
    
    /**
     * @param PrepaidMeter $meter
     * @return string
     * @throws BadRequestException
     * @throws GeneralException
     */
    public function buy(PrepaidMeter $meter): string;
    
    /**
     * @param $transaction
     * @return Transaction
     */
    public function status($transaction): string;
    
    /**
     * @return string
     */
    public function getClientName(): string;
}