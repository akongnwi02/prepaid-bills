<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 4/19/20
 * Time: 1:30 AM
 */

namespace App\Jobs;


use App\Models\Transaction;
use App\Services\Clients\ClientProvider;
use App\Services\Constants\QueueConstants;
use App\Services\Constants\TransactionConstants;
use Illuminate\Support\Facades\Log;

class VerificationJob extends Job
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
     * Timeout
     * @var int
     */
    public $timeout = 150;
    /**
     * Number of retries
     * @var int
     */
    public $tries = 5;
    
    /**
     * Create a new job instance.
     * @param $transaction
     */
    
    public function __construct($transaction)
    {
        $this->transaction = $transaction;
    }
    
    /**
     *
     */
    public function handle()
    {
        Log::info("{$this->getJobName()}: Processing new status verification job", [
            'status' => $this->transaction->status,
            'transaction.id' => $this->transaction->id,
            'destination' => $this->transaction->destination,
        ]);
    
        $this->transaction->verification_attempts = $this->attempts();
        $this->transaction->status = TransactionConstants::VERIFICATION;
        $this->transaction->save();
    
        try {
            $token = $this->client($this->transaction->service_code)->status($this->transaction);
            $this->transaction->asset = $token;
            $this->transaction->status = TransactionConstants::SUCCESS;
            $this->transaction->message = 'Transaction updated to success by verification worker';
            $this->transaction->save();
            
            Log::info("{$this->getJobName()}: Status updated to success, inserting transaction to callback queue", [
                'status' => $this->transaction->status,
                'asset' => $this->transaction->asset,
                'transaction.id' => $this->transaction->id,
                'destination' => $this->transaction->destination,
            ]);
            /*
             * Transaction was found successful after status verification.
             * Insert to callback queue
             */
            dispatch(new CallbackJob($this->transaction))->onQueue(QueueConstants::CALLBACK_QUEUE);
            
            $this->delete();
            
        } catch (\Exception $e) {
            Log::info("{$this->getJobName()}: Status verification attempt failed", [
                'error message' => $e->getMessage(),
                'status' => $this->transaction->status,
                'transaction.id' => $this->transaction->id,
                'destination' => $this->transaction->destination,
                'callback_url' => $this->transaction->callback_url,
                'attempts' => $this->attempts(),
            ]);
            /*
             * Delay job before attempting the next status verification
             */
            $this->release($this->attempts() * 2);
        }
    }
    
    public function failed($exception = null)
    {
        $this->transaction->status = TransactionConstants::FAILED;
        $this->transaction->message = 'Transaction failed automatically while verifying status';
        $this->transaction->save();
        Log::alert("{$this->getJobName()}: Transaction failed automatically during status check. Inserted into CALLBACK queue", [
            'status' => $this->transaction->status,
            'transaction.id' => $this->transaction->id,
            'destination' => $this->transaction->destination,
            'exception' => $exception,
        ]);
    
        dispatch(new CallbackJob($this->transaction))->onQueue(QueueConstants::CALLBACK_QUEUE);
    }
    
    public function getJobName()
    {
        return class_basename($this);
    }
}