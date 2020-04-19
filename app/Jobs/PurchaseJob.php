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
     */
    public function handle(PrepaidMeter $meter)
    {
        Log::info("{$this->getJobName()}: Processing new purchase job" ,[
            'status'         => $this->transaction->status,
            'transaction.id' => $this->transaction->id,
            'destination' => $this->transaction->destination
        ]);
        $this->transaction->status = TransactionConstants::PROCESSING;
        $this->transaction->purchase_attempts = $this->attempts();
        $this->transaction->save();
        
        $meter->setAmount($this->transaction->amount)
            ->setMeterCode($this->transaction->destination)
            ->setServiceCode($this->transaction->service_code)
            ->setPhone($this->transaction->phone)
            ->setIntId($this->transaction->internal_id);
        
        try {
            $token = $this->client($meter->getServiceCode())->buy($meter);
            $this->transaction->asset = $token;
            $this->transaction->status = TransactionConstants::SUCCESS;
            $this->transaction->message = 'Transaction completed successfully';
            
            Log::info("{$this->getJobName()}: Transaction effectuated successfully. Inserted into CALLBACK queue", [
                'status' => $this->transaction->status,
                'transaction.id' => $this->transaction->id,
                'destination' => $this->transaction->destination
            ]);
            
            /*
             *
             * Transaction successful, dispatch to callback queue
             *
             */
            dispatch(new CallbackJob($this->transaction))->onQueue(QueueConstants::CALLBACK_QUEUE);
    
        } catch (BadRequestException $exception) {
            // Delete the job for any of the reasons above
            $this->transaction->status = TransactionConstants::FAILED;
            $this->transaction->error = $exception->getMessage();
            $this->transaction->message = $exception->getMessage();
            $this->transaction->error_code =$exception->error_code();
            
            Log::info("{$this->getJobName()}: Transaction failed due to client error. Inserted into CALLBACK queue", [
                'status'         => $this->transaction->status,
                'transaction.id' => $this->transaction->id,
                'destination' => $this->transaction->destination,
                'exception' => $exception,
            ]);
            
            /*
             *
             * Transaction failed due to a client error, dispatch to callback queue
             *
             */
            dispatch(new CallbackJob($this->transaction))->onQueue(QueueConstants::CALLBACK_QUEUE);
            
        } catch(\Exception $exception) {
    
            // Delete the job for any of the reasons above
            $this->transaction->status = TransactionConstants::ERRORED;
            $this->transaction->error = $exception->getMessage();
            $this->transaction->message = 'Transaction failed unexpectedly';
            $this->transaction->error_code = ErrorCodesConstants::GENERAL_CODE;
            
            Log::alert("{$this->getJobName()}: Transaction failed unexpectedly. Inserted into VERIFICATION queue", [
                'status' => $this->transaction->status,
                'transaction.id' => $this->transaction->id,
                'destination' => $this->transaction->destination,
                'exception' => $exception,
            ]);
            
            /*
             *
             * Transaction in unknown state, dispatch to status verification queue
             *
             */
            dispatch(new VerificationJob($this->transaction))->onQueue(QueueConstants::STATUS_QUEUE);
            
        }
        
        /*
         * Update Transaction
         */
        $this->transaction->save();
    
        /*
         * Delete job from queue on first attempt
         */
        $this->delete();
    
    }
    
    public function failed(\Exception $exception = null)
    {
        $this->transaction->status = TransactionConstants::ERRORED;
        $this->transaction->message = 'Transaction failed automatically';
        $this->transaction->error = $exception->getMessage();
        $this->transaction->save();
        Log::alert("{$this->getJobName()}: Transaction failed automatically. Inserted into VERIFICATION queue", [
            'status' => $this->transaction->status,
            'transaction.id' => $this->transaction->id,
            'destination' => $this->transaction->destination,
            'exception' => $exception,
        ]);
    
        dispatch(new VerificationJob($this->transaction))->onQueue(QueueConstants::STATUS_QUEUE);
    
    }
    
    public function getJobName()
    {
        return class_basename($this);
    }
}

