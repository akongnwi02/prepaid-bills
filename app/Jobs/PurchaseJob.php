<?php

namespace App\Jobs;

use App\Exceptions\BadRequestException;
use App\Models\Transaction;
use App\Services\Clients\ClientProvider;
use App\Services\Constants\ErrorCodesConstants;
use App\Services\Constants\QueueConstants;
use App\Services\Constants\TransactionConstants;
use App\Services\Objects\PrepaidMeter;
use Illuminate\Support\Facades\Log;

class PurchaseJob extends Job
{
    use ClientProvider;
    
    /**
     * @var Transaction
     */
    public $transaction;
    
    /**
     * Avoid processing deleted jobs
     */
    public $deleteWhenMissingModels = true;
    
    /**
     * Number of retries
     * @var int
     */
    public $tries = 1;
    
    /**
     * Timeout
     * @var int
     */
    public $timeout = 300;
    
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
     * @param PrepaidMeter $meter
     * @return void
     * @throws \App\Exceptions\GeneralException
     */
    public function handle(PrepaidMeter $meter)
    {
        Log::info("{$this->getJobName()}: Processing new purchase job", [
            'status'         => $this->transaction->status,
            'transaction.id' => $this->transaction->id,
            'destination'    => $this->transaction->destination
        ]);
        $this->transaction->status            = TransactionConstants::PROCESSING;
        $this->transaction->purchase_attempts = $this->attempts();
        $this->transaction->save();
        
        $meter->setAmount($this->transaction->amount)
            ->setMeterCode($this->transaction->destination)
            ->setServiceCode($this->transaction->service_code)
            ->setPhone($this->transaction->phone)
            ->setIntId($this->transaction->internal_id);
        
        try {
            $token = $this->client($meter->getServiceCode())->buy($meter);
            
            $this->transaction->asset   = $token;
            $this->transaction->status  = TransactionConstants::SUCCESS;
            $this->transaction->message = 'Transaction completed successfully';
            $this->transaction->save();
            
            Log::info("{$this->getJobName()}: Transaction effectuated successfully. Inserted into CALLBACK queue", [
                'status'         => $this->transaction->status,
                'transaction.id' => $this->transaction->id,
                'destination'    => $this->transaction->destination
            ]);
            
            /*
             * Transaction successful, dispatch to callback queue
             */
            dispatch(new CallbackJob($this->transaction))->onQueue(QueueConstants::CALLBACK_QUEUE);
            
        } catch (BadRequestException $exception) {
            $this->transaction->status     = TransactionConstants::FAILED;
            $this->transaction->error      = $exception->getMessage();
            $this->transaction->message    = 'Transaction failed due to a client error';
            $this->transaction->error_code = $exception->error_code();
            $this->transaction->save();
            
            Log::info("{$this->getJobName()}: Transaction failed due to client error. Inserted into CALLBACK queue", [
                'status'         => $this->transaction->status,
                'transaction.id' => $this->transaction->id,
                'destination'    => $this->transaction->destination,
                'exception'      => $exception,
            ]);
            
            /*
             * Transaction failed due to a client error, dispatch to callback queue
             */
            dispatch(new CallbackJob($this->transaction))->onQueue(QueueConstants::CALLBACK_QUEUE);
        }
    }
    
    /**
     * @param \Exception|null $exception
     */
    public function failed(\Exception $exception = null)
    {
        $this->transaction->status     = TransactionConstants::ERRORED;
        $this->transaction->message    = 'Transaction failed unexpectedly';
        $this->transaction->error      = $exception->getMessage();
        $this->transaction->error_code = ErrorCodesConstants::GENERAL_CODE;
        $this->transaction->save();
        Log::emergency("{$this->getJobName()}: Transaction failed unexpectedly during purchase. Inserted into VERIFICATION queue", [
            'transaction.status'      => $this->transaction->status,
            'transaction.id'          => $this->transaction->id,
            'transaction.destination' => $this->transaction->destination,
            'transaction.amount'      => $this->transaction->amount,
            'transaction.message'     => $this->transaction->message,
            'transaction.error'       => $this->transaction->error,
            'transaction.error_code'  => $this->transaction->error_code,
            'transaction.external_id' => $this->transaction->external_id,
            'exception'               => $exception,
        ]);
        
        /*
         * Transaction failed due to a unexpected error, dispatch to verification queue
         */
        dispatch(new StatusJob($this->transaction))->onQueue(QueueConstants::STATUS_QUEUE);
    }
    
    public function getJobName()
    {
        return class_basename($this);
    }
}

