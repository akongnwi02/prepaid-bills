<?php

namespace App\Http\Controllers;

use App\Exceptions\DuplicateException;
use App\Exceptions\GeneralException;
use App\Http\Resources\PrepaidMeterResource;
use App\Jobs\PurchaseJob;
use App\Models\Transaction;
use App\Services\Clients\ClientTrait;
use App\Services\Objects\PrepaidMeter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Laravel\Lumen\Http\Request;

class TransactionController extends Controller
{
    use ClientTrait;
    
    /**
     * @param Request $request
     * @return PrepaidMeterResource
     * @throws GeneralException
     * @throws \App\Exceptions\BadRequestException
     * @throws \App\Exceptions\ForbiddenException
     * @throws \App\Exceptions\NotFoundException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function search(Request $request)
    {
        $this->validate($request, [
            'destination' => ['required', 'string', 'min:7'],
            'service__code' => ['required', 'string', 'min:3',],
        ]);
        
        $meter = $this->client($request['destination_code'])->search($request['destination']);
        
        return new PrepaidMeterResource($meter);
    
    }
    
    /**
     * @param Request $request
     * @param Transaction $transaction
     * @return Transaction
     * @throws DuplicateException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function execute(Request $request, Transaction $transaction)
    {
        $this->validate($request, [
            'destination' => ['required', 'string', 'min:7'],
            'service__code' => ['required', 'string', 'min:3',],
            'ext_id' => ['required', 'string', Rule::unique('transactions', 'external_id')],
            'amount' => ['required', 'regex:/^(?:\d{1,3}(?:,\d{3})+|\d+)(?:\.\d+)?$/'],
            'callback_url' => ['required', 'url'],
        ]);
    
        /** @var PrepaidMeter $meter */
        $meter = Cache::pull($request['search_id']);
    
        if (! $meter) {
            throw new DuplicateException('This transaction is no longer available. May have expired or already processed');
        }
        
        $transaction->
        // TODO
        // SET THE TRANSACTION OBJECT BEFORE DISPATCHING THE JOB
        dispatch(new PurchaseJob($transaction));
    
        return response()->json($transaction);
    }
    
    public function status(Transaction $transaction)
    {
    
    }

}
