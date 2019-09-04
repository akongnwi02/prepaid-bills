<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 8/31/19
 * Time: 2:03 PM
 */

namespace App\Repositories;

use App\Exceptions\ResourceNotFoundException;
use App\Models\Transaction;
use App\Services\Constants;
use App\Services\Meter;

class TransactionRepository
{
    /**
     * @var
     */
    protected $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * @param $meter
     * @param $data
     * @return Transaction
     * @throws ResourceNotFoundException
     */
    public function create($meter, array $data): Transaction
    {

        if ($meter && $meter instanceof Meter) {

            $energy = $this->calculateEnergy($data['amount'], $meter);

            return Transaction::create([
                'internal_id'  => $meter->getInternalId(),
                'meter_code'   => $meter->getMeterCode(),
                'meter_id'     => $meter->getContractId(),
                'amount'       => $data['amount'],
                'external_id'  => $data['externalId'],
                'status'       => Constants::CREATED,
                'energy'       => $energy
            ]);

        }

        throw new ResourceNotFoundException(Transaction::class, $data['internalId']);
    }

    /**
     * @param $amount
     * @param $meter
     * @return double
     */
    public function calculateEnergy($amount, Meter $meter)
    {
        return round($amount / ($meter->getTariff() * (1 + $meter->getVat()/100))/$meter->getPtct(), 2);
    }

}