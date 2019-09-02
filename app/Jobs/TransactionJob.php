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
     */
    public function handle(HexcellClient $client)
    {
        \Log::info('Job started', ['txId' => $this->transaction->internal_id]);
//        $token = $client->generateToken($this->transaction);
        dd($this->transaction);
    }
}

