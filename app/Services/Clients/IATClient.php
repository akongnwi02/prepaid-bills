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
        $client = $this->getHttpClient();
        try {
            $response = $client->request('GET', '/search', [
                'query' => ['meter_code' => $meterCode],
                'headers' => ['x-api-key' => config('app.services.iat.key')]
            ]);
        } catch (GuzzleException $exception) {
            
            throw new GeneralException('Error connecting to service provider: ' . $exception->getMessage());
        }
    
        $content = $response->getBody()->getContents();
        $status = $response->getStatusCode();
    
        Log::debug('response from service provider', [
            'provider' => config('app.services.iat.code'),
            'response' => $content
        ]);
    
        $body = json_decode($content);
    
        if ($status == 200) {
            $meter = new PrepaidMeter();
            $meter->setServiceCode(config('app.services.iat.code'))
                ->setMeterCode($meterCode)
                ->setName($body->name)
                ->setAddress($body->address);
            return $meter;
            
        } else if($status == 404){
            throw new NotFoundException('meter_code', $meterCode);
        } else if ($status == 403) {
            throw new ForbiddenException($body->message);
        } else if ($status == 422) {
            throw new BadRequestException($body->message);
        } else {
            throw new GeneralException($body->messge);
        }
    }
    
    public function buy($meterCode, $amount): string
    {
    
    }
    
    
    public function getHttpClient()
    {
        return new Client([
            'base_uri'        => config('app.services.iat.url'),
            'timeout'         => 120,
            'connect_timeout' => 120,
            'allow_redirects' => true,
        ]);
    }
}