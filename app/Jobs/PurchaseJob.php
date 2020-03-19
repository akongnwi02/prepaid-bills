<?php

namespace App\Jobs;

use App\Services\Clients\ClientTrait;

class PurchaseJob extends Job
{
    use ClientTrait;
    
    public $transaction;
    
    /**
     * Create a new job instance.
     * @param $transaction
     */
    public function __construct($transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        return response;
    }
}

