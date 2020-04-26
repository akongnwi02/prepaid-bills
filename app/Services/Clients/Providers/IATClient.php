<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 3/14/20
 * Time: 8:10 PM
 */

namespace App\Services\Clients\Providers;

use App\Exceptions\BadRequestException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\GeneralException;
use App\Exceptions\NotFoundException;
use App\Models\Transaction;
use App\Services\Clients\ClientInterface;
use App\Services\Constants\ErrorCodesConstants;
use App\Services\Objects\PrepaidMeter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use PHPUnit\Runner\Exception;

class IATClient implements ClientInterface
{
    /**
     * @param $meterCode
     * @return PrepaidMeter
     * @throws BadRequestException
     * @throws GeneralException
     * @throws GuzzleException
     */
    public function search($meterCode): PrepaidMeter
    {
    
        $query = ['meter_code' => $meterCode];
    
        Log::debug("{$this->getClientName()}: Sending request to service provider", [
            'query' => $query
        ]);
        
        $httpClient = $this->getHttpClient();
        try {
            $response = $httpClient->request('GET', '/api/search', [
                'query' => $query,
            ]);
        } catch (ClientException $exception) {
            $response = $exception->getResponse();
        } catch (\Exception $exception) {
            throw new GeneralException(ErrorCodesConstants::SERVICE_PROVIDER_CONNECTION_ERROR, 'Error connecting to service provider: ' . $exception->getMessage());
        }
        
        $content = $response->getBody()->getContents();
        
        Log::debug("{$this->getClientName()}: Response from service provider", [
            'provider' => config('app.services.iat.code'),
            'response' => $content
        ]);
        
        $body = json_decode($content);
        if ($response->getStatusCode() == 200) {
            $meter = new PrepaidMeter();
            $meter->setServiceCode(config('app.services.iat.code'))
                ->setMeterCode($meterCode)
                ->setName($body->landlord)
                ->setAddress(implode(', ', array_filter([$body->address, $body->location, $body->area])));
            return $meter;
        } else {
            return $this->handleErrorResponse($body);
        }
    }
    
    /**
     * @param PrepaidMeter $meter
     * @return string
     * @throws BadRequestException
     * @throws GeneralException
     */
    public function buy(PrepaidMeter $meter): string
    {
        
        $json = [
            'meter_code' => $meter->getMeterCode(),
            'amount'     => $meter->getAmount(),
            'external_id'=> $meter->getIntId(),
            // phone not required yet
            'phone'      => $meter->getPhone(),
        ];
    
        Log::debug("{$this->getClientName()}: Sending request to service provider",[
            'json' => $json
        ]);
    
        $httpClient = $this->getHttpClient();
        
        try {
            $response = $httpClient->request('POST', '/api/buy', [
                'json' => $json
            ]);
        } catch (ClientException $exception) {
            $response = $exception->getResponse();
            
        } catch (GuzzleException $exception) {
            throw new GeneralException(ErrorCodesConstants::SERVICE_PROVIDER_CONNECTION_ERROR,
                'Error connecting to service provider: ' . $exception->getMessage()
            );
        }
        
        $content = $response->getBody()->getContents();
        
        Log::debug("{$this->getClientName()}: Response from service provider", [
            'provider' => config('app.services.iat.code'),
            'meter code' => $meter->getMeterCode(),
            'body' => json_decode($content),
            'response' => $content
        ]);
        
        $body = json_decode($content);
    
        if ($response->getStatusCode() == 200 || $response->getStatusCode() == 201) {
            
            if (!empty($body->token)) {
                return $body->token;
            }
        } else {
            $this->handleErrorResponse($body);
        }
    }
    
    /**
     * @param $transaction
     * @return Transaction
     * @throws GeneralException
     * @throws GuzzleException
     */
    public function status($transaction): string
    {
        Log::info("{$this->getClientName()}: Sending status check request to service provider", [
            'meter code' => $transaction->destination,
            'provider' => config('app.services.iat.code'),
            'transaction.id' => $transaction->id,
            'transaction.status' => $transaction->status,
        ]);
    
        $httpClient = $this->getHttpClient();
    
        try {
            $response = $httpClient->request('GET', "/api/status/$transaction->internal_id");
        } catch (\Exception $exception) {
            Log::error("{$this->getClientName()}: Error Response from service provider", [
                'provider' => config('app.services.iat.code'),
                'meter code' => $transaction->destination,
                'error message' => $exception->getMessage(),
            ]);
            throw new GeneralException(ErrorCodesConstants::GENERAL_CODE, $exception->getMessage());
        }
    
        $content = $response->getBody()->getContents();
        
        Log::debug("{$this->getClientName()}: Response from service provider", [
            'provider' => config('app.services.iat.code'),
            'meter code' => $transaction->destination,
            'body' => json_decode($content),
            'response' => $content,
        ]);
    
        $body = json_decode($content);
    
        if ($response->getStatusCode() == 200 || $response->getStatusCode() == 201) {
            
            if (!empty($body->token)) {
                return $body->token;
            }
        } else {
            throw new GeneralException(ErrorCodesConstants::GENERAL_CODE, 'Token could not be extracted from response response');
        }
    }
    
    /**
     * @param $body
     * @throws BadRequestException
     * @throws GeneralException
     */
    public function handleErrorResponse($body)
    {
        $status = @$body->code;
        if ($status == 404) {
            throw new BadRequestException(ErrorCodesConstants::METER_CODE_NOT_FOUND, $body->message);
        } else if ($status == 403) {
            throw new BadRequestException(ErrorCodesConstants::DEACTIVATED_METER, $body->message);
        } else if ($status == 422) {
            throw new BadRequestException(ErrorCodesConstants::INVALID_METER_CODE, $body->message);
        } else {
            throw new GeneralException(ErrorCodesConstants::GENERAL_CODE, $body->message);
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
            'headers'         => [
                'x-api-key' => config('app.services.iat.key'),
                'accept'    => 'application/json'
            ],
        ]);
    }
    
    public function getClientName(): string
    {
        return class_basename($this);
    }
}