<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 8/31/19
 * Time: 2:03 PM
 */

namespace App\Repositories;

use App\Exceptions\ConnectionException;
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

            $transaction = Transaction::create([
                'internal_id'  => $meter->getInternalId(),
                'meter_code'   => $meter->getMeterCode(),
                'meter_id'     => $meter->getContractId(),
                'amount'       => $data['amount'],
                'external_id'  => $data['externalId'],
                'callback_url' => $data['callbackUrl'],
                'status'       => Constants::CREATED,
            ]);

            return $transaction;

        }

        throw new ResourceNotFoundException(Transaction::class, $data['internalId']);
    }

    /**
     * @param $txId
     * @param $meter
     */
    public function calculateEnergy($txId, $meter)
    {

    }

    public function getByTxId()
    {
//        $transaction =
//        return $transaction =
    }
}