<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Jobs\TransactionJob;
use App\Rules\HexcellCode;
use Illuminate\Http\Request;
use App\Services\HexcellClient;
use App\Http\Resources\MeterResource;
use App\Exceptions\ResourceNotFoundException;
use App\Repositories\TransactionRepository;

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
    public function __construct(HexcellClient $client, \Redis $redis, TransactionRepository $repository)
    {
        $this->client = $client;
        $this->redis  = $redis;
        $this->repository = $repository;

        // correctly facing an issue making redis config available to
        // the redis client due to error in installing illuminate/redis
        // hence setting the redis host here PS: Bad practice
        $redis->connect(config('database.redis.cache.host'));

    }

    /**
     * @param Request $request
     * @return MeterResource
     * @throws ResourceNotFoundException
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
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

        $this->redis->set($meter->getInternalId(), json_encode($meter));

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

        $meter = json_decode($this->redis->get($request['internalId']));

        $transaction = $this->repository->create($meter, $request->input());

        $this->redis->del($request['internalId']);

        $this->dispatch(TransactionJob::class);

        return new TransactionResource($transaction);
    }

}
