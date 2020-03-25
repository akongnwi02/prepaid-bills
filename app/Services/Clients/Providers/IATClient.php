<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 3/14/20
 * Time: 8:10 PM
 */

namespace App\Services\Clients;

use App\Exceptions\BadRequestException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\GeneralException;
use App\Exceptions\NotFoundException;
use App\Services\Objects\PrepaidMeter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class IATClient implements ClientInterface
{
    /**
     * @param $meterCode
     * @return PrepaidMeter
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws GeneralException
     * @throws NotFoundException
     */
    public function search($meterCode): PrepaidMeter
    {
        $httpClient = $this->getHttpClient();
        try {
            $response = $httpClient->request('GET', 'search', [
                'query' => ['meter_code' => $meterCode],
            ]);
        } catch (GuzzleException $exception) {
            throw new GeneralException('Error connecting to service provider: ' . $exception->getMessage());
        }
        $content = $response->getBody()->getContents();
        
        Log::debug('response from service provider', [
            'provider' => config('app.services.iat.code'),
            'response' => $content
        ]);
        
        if ($response->getStatusCode() == 200) {
            $body = json_decode($content);
            $meter = new PrepaidMeter();
            $meter->setServiceCode(config('app.services.iat.code'))
                ->setMeterCode($meterCode)
                ->setName($body->name)
                ->setAddress($body->address);
            return $meter;
        } else {
            return $this->handleErrorResponse($response);
        }
    }
    
    /**
     * @param PrepaidMeter $meter
     * @return string
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws GeneralException
     * @throws NotFoundException
     */
    public function buy(PrepaidMeter $meter): string
    {
        $httpClient = $this->getHttpClient();
        try {
            $response = $httpClient->request('POST', '/pay', [
                'json' => [
                    'meter_code' => $meter->getMeterCode(),
                    'amount'     => $meter->getAmount(),
                    'phone'      => $meter->getPhone(),
                ]
            ]);
        } catch (GuzzleException $exception) {
            throw new GeneralException('Error connecting to service provider: ' . $exception->getMessage());
        }
        
        $content = $response->getBody()->getContents();
        
        Log::debug('response from service provider', [
            'provider' => config('app.services.iat.code'),
            'response' => $content
        ]);
        
        if ($response->getStatusCode() == 200) {
            $body = json_decode($content);
            return $body->token;
        } else {
            return $this->handleErrorResponse($response);
        }
    }
    
    /**
     * @param $response
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws GeneralException
     * @throws NotFoundException
     */
    public function handleErrorResponse($response)
    {
        $body = json_decode($response->getBody()->getContents());
        $status = $response->getStatusCode();
        
        if ($status == 404) {
            throw new NotFoundException('meter_code');
        } else if ($status == 403) {
            throw new ForbiddenException($body->message);
        } else if ($status == 422) {
            throw new BadRequestException($body->message);
        } else {
            throw new GeneralException($body->messge);
        }
    }
    
    /**
     * @return Client
     */
    public function getHttpClient()
    {
        return new Client([
            'base_uri'        => config('app.services.iat.url'),
            'timeout'         => 120,
            'connect_timeout' => 120,
            'allow_redirects' => true,
            'headers'         => ['x-api-key' => config('app.services.iat.key')],
        ]);
    }
}