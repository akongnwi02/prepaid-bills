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
     * @throws ConnectionException
     */
    public function create($meter, $data): Transaction
    {

        if ($meter && $meter instanceof Meter) {

            $transaction               = new Transaction();
            $transaction->internal_id  = $meter->getInternalId();
            $transaction->meter_code   = $meter->getMeterCode();
            $transaction->external_id  = $data['externalId'];
            $transaction->amount       = $data['amount'];
            $transaction->callback_url = $data['callbackUrl'];
            $transaction->status       = Constants::CREATED;

            if ($transaction->save()) {
                return $transaction;
            }

            throw new ConnectionException('mysql', 'save');
        }

        throw new ResourceNotFoundException(Transaction::class, $data['internalId']);
    }
}