<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Services\HexcellClient;

class TransactionJob extends Job
{

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * Create a new job instance.
     *
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     *
     * @param HexcellClient $client
     * @return void
     * @throws \App\Exceptions\TokenGenerationException
     */
    public function handle(HexcellClient $client)
    {
        \Log::info('Job started', ['txId' => $this->transaction->internal_id]);
        $token = $client->generateToken([
            'meterId' => $this->transaction->meter_id,
            'amount' => $this->transaction->amount,
            'energy' => $this->transaction->energy
        ]);

        $this->transaction->token = $token;

        if (! $this->transaction->save()) {

        }

    }
}

