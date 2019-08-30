<?php

namespace App\Http\Controllers;

use App\Exceptions\ResourceNotFoundException;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\MeterResource;
use App\Rules\HexcellCode;
use App\Services\HexcellClient;
use Illuminate\Http\Request;

class SearchMeterController extends Controller
{
    private $client;
    /**
     * Create a new controller instance.
     *
     * @param HexcellClient $client
     */
    public function __construct(HexcellClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param Request $request
     * @return ErrorResource|MeterResource|\Illuminate\Http\JsonResponse
     * @throws ResourceNotFoundException
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function search(Request $request)
    {

        \Log::info('SearchMeterController: New search request', [
            'input' => $request->input(),
            'ip' => $request->getClientIp()
        ]);

        $this->validate($request, ['meterCode' => ['required', 'string', new HexcellCode()]]);

        $meter = $this->client->searchMeter($request['meterCode']);

        return new MeterResource($meter);

    }
}
