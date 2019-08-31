<?php

namespace App\Jobs;

use App\Services\HexcellClient;

class TransactionJob extends Job
{
    /**
     * @var HexcellClient
     */
    protected $client;

    /**
     * Create a new job instance.
     *
     * @param HexcellClient $client
     */
    public function __construct(HexcellClient $client)
    {
        $this->client = $client;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $transaction = $this->client->generateToken();
    }
}
