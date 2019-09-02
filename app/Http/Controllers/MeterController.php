<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Jobs\TransactionJob;
use App\Rules\HexcellCode;
use App\Services\Constants;
use Illuminate\Http\Request;
use App\Services\HexcellClient;
use App\Http\Resources\MeterResource;
use App\Exceptions\ResourceNotFoundException;
use App\Repositories\TransactionRepository;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

class MeterController extends Controller
{
    /**
     * @var HexcellClient
     */
    protected $client;

    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * @var
     */
    protected $repository;

    /**
     * Create a new controller instance.
     *
     * @param HexcellClient $client
     * @param \Redis $redis
     * @param TransactionRepository $repository
     */
    public function __construct(HexcellClient $client,  TransactionRepository $repository)
    {
        $this->client = $client;
        $this->repository = $repository;
    }

    /**
     * @param Request $request
     * @return MeterResource
     * @throws ResourceNotFoundException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function search(Request $request)
    {

        \Log::info('MeterController: New search request', [
            'input' => $request->input(),
            'ip'    => $request->getClientIp(),
        ]);

        $this->validate($request, ['meterCode' => ['required', 'string', new HexcellCode()]]);

        $meter = $this->client->searchMeter($request['meterCode']);

        \Log::info('MeterController: Search Successful', [
            'meter_code'        => $meter->getMeterCode(),
            'meter_internal_id' => $meter->getInternalId(),
        ]);

        Redis::set($meter->getInternalId(), serialize($meter));

        return new MeterResource($meter);

    }

    /**
     * @param Request $request
     * @return TransactionResource
     * @throws ResourceNotFoundException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \App\Exceptions\ConnectionException
     */
    public function token(Request $request)
    {
        \Log::info('MeterController: New token request', [
            'input' => $request->input(),
            'ip'    => $request->getClientIp()
        ]);

        $this->validate($request, [
                'internalId'  => ['required', 'string',],
                'amount'      => ['required', 'regex:/^\d+(\.\d{1,2})?$/',],
                'callbackUrl' => ['required', 'url'],
                'externalId'  => ['required', 'string']
            ]
        );

        $meter = unserialize(Redis::get($request['internalId']));

        $transaction = $this->repository->create($meter, $request->input());

        Redis::del($request['internalId']);

        Queue::pushOn(Constants::TOKEN_QUEUE, new TransactionJob($transaction));

        return new TransactionResource($transaction);
    }

}
